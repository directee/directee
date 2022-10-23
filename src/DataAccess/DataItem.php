<?php

namespace Directee\DataAccess;

use Doctrine\DBAL\Connection;

/**
 *
 */
class DataItem
{
    private $resource;
    private $db;
    private $keyName;
    private $attribute;

    public function __construct(string $resource, string $keyName, Connection $connection)
    {
        $this->resource = $resource;
        $this->db = $connection;
        $this->keyName = $keyName;
        $this->attribute = [];
    }

    public function getKeyName(): string
    {
        return $this->keyName;
    }

    public function hasId(): bool
    {
        return (bool) $this->attribute[$this->getKeyName()];
    }

    public function getId(): string
    {
        return $this->attribute[$this->getKeyName()] ?? '';
    }

    public function setId(string $id)
    {
        $this->attribute[$this->getKeyName()] = $id;
    }

    public function getAttribute(string $name)
    {
        return $this->attribute[$name];
    }

    public function setAttribute(string $name, $value)
    {
        $this->attribute[$name] = $value;
    }

    public function asArray(): array
    {
        return $this->attribute;
    }

    public function fromArray($data): self
    {
        $this->attribute = $data;
        return $this;
    }

    public function save()
    {
        $updated_id = $this->getId();
        if ($this->hasId()) {
            $query = $this->db->createQueryBuilder()->update($this->resource);
            $query->where("$this->keyName = :id")->setParameter('id', $updated_id);
            foreach($this->attribute as $nm => $vl) {
                $prm = ":$nm";
                $query->set($nm,$prm)->setParameter($prm,$vl);
            }
            $query->executeStatement();
        } else {
            $query = $this->db->createQueryBuilder()->insert($this->resource);
            foreach($this->attribute as $nm => $vl) {
                $prm = ":$nm";
                $query->setValue($nm,$prm)->setParameter($prm,$vl);
            }
            $resu = $query->executeQuery()->fetchAllAssociative();
            $updated_id = $this->db->lastInsertId($this->resource);
        }
        $row = $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->resource)
            ->where("$this->keyName = :id")
            ->setParameter('id', $updated_id)
            ->executeQuery()
            ->fetchAssociative()
        ;
        $this->fromArray($row);
    }

    public function delete()
    {
        if ($this->hasId()) {
            $query = $this->db->createQueryBuilder()->delete($this->resource);
            $query->where("$this->keyName = :id")->setParameter('id', $this->getId());
            $query->executeStatement();
        }
    }
}
