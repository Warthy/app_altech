<?php
namespace Altech\Model\Repository;

use Altech\Model\Entity\EntityInterface;
use Altech\Model\Entity\Ticket;
use Altech\Model\Entity\User;
use App\Component\Repository;
use PDO;

class TicketRepository extends Repository
{
    const TABLE_NAME = "ticket";
    const ENTITY = Ticket::class;

    public function findAllByClient(User $client){
        $stmt = $this->pdo->prepare('SELECT * FROM '. self::TABLE_NAME .' WHERE client_id = :client_id');
        $stmt->bindValue(':client_id', $client->getId(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS, self::ENTITY);
    }

    public function update(EntityInterface $entity): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Ticket $ticket
     * @return Ticket
     */
    public function insert($ticket): Ticket
    {
        $stmt =$this->pdo->prepare(
            'INSERT INTO ' . self::TABLE_NAME . ' (description, subject, client_id, closed, open_at) '.
            'VALUES (:description, :subject, :client_id, 0, NOW())' );
        $stmt->bindValue(':client_id', $ticket->getClientId() ,PDO::PARAM_INT);
        $stmt->bindValue(':subject', $ticket->getSubject() ,PDO::PARAM_STR);
        $stmt->bindValue(':description', $ticket->getDescription() ,PDO::PARAM_STR);

        $stmt->execute();
        $ticket->setId($this->pdo->lastInsertId());

        return $ticket;
    }
}