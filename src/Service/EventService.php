<?php

namespace Dreamstats\Service;

use Dreamstats\Model\Event;

class EventService extends PdoService
{
    public function delete(int $id)
    {
        $statement = $this->pdo->prepare("DELETE FROM event WHERE id = :id");
        $statement->execute([':id' => $id]);
    }

    public function insert(Event $event)
    {
        $statement = $this->pdo->prepare("INSERT INTO event (name, date) VALUES (:name, :date)");
        $statement->execute([':name' => $event->name, ':date' => $event->date]);
    }

    public function findAll()
    {
        $query = "SELECT * FROM event ORDER BY id DESC";
        $result = $this->pdo->query($query);
        $events = [];
        foreach ($result as $row) {
            $event = new Event();
            $event->id = $row['id'];
            $event->name = $row['name'];
            $event->date = $row['date'];
            $events[] = $event;
        }

        return $events;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM event WHERE id = :id";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $id]);
        $result = $statement->fetchAll();

        $event = null;
        if ($result) {
            $event = new Event();
            foreach ($result as $row) {
                $event->id = $row['id'];
                $event->name = $row['name'];
                $event->date = $row['date'];
            }
        }

        return $event;
    }
}