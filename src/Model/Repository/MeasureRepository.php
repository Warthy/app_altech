<?php
namespace Altech\Model\Repository;


use Altech\Model\Entity\Measure;
use Altech\Model\Entity\EntityInterface;
use App\Component\Repository;


class MeasureRepository extends Repository
{
    const TABLE_NAME = "measure";
    const ENTITY = Measure::class;

    public function findById(int $id): Measure
    {
        /** @var Measure $measure */
        $measure = parent::findById($id);
        $stmt = $this->pdo->prepare('SELECT * FROM ' . CandidateRepository::TABLE_NAME . ' WHERE id = :id');
        $stmt->execute(["id" => $measure->candidate_id]);

        $measure->setCandidate($stmt->fetchObject(CandidateRepository::ENTITY));
        return $measure;
    }

    public function findAllBelongingToCandidate(): array
    {
        return parent::findAll(); // TODO: Change the autogenerated stub
    }

    /**
     * @param Measure $measure
     * @return Measure
     */
    public function insert(Measure $measure)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO' . self::TABLE_NAME . 
            '(heartbeat, temperature, conductivity, visual_unexpected_reflex, visual_expected_reflex, sound_unexpected_reflex, sound_expected_reflex, tonality_recognition, date_measured, candidate_id' .
            'VALUES (:heartbeat, :temperature, :conductivity, :visual_unexpected_reflex, :visual_expected_reflex, :sound_unexpected_reflex, :sound_expected_reflex, :tonality_recognition, :date_measured, :candidate_id)'
        );

        

        $stmt->execute([
            'heartbeat' => $measure->getHeartBeat(),
            'temperature' => $measure->getTemperature(),
            'conductivity' => $measure->getConductivity(),
            'visual_unexpected_reflex' => $measure->getVisualUnexpectedReflex(),
            'visual_expected_reflex' => $measure->getVisualExpectedReflex(),
            'sound_unexpected_reflex' => $measure->getSoundUnexpectedReflex(),
            'sound_expected_reflex' => $measure->getSoundExpectedReflex(),
            'tonality_recognition' => $measure->getTonalityRecognition(),
            'date_measured' => $measure->getDate_measured(),
            'candidate_id' => $measure->getCandidate()->getId()
        ]);
        
        $measure->setId($this->pdo->lastInsertId());
        return $measure;        
    }

    public function update(EntityInterface $entity): void
    {
        // TODO: Implement update() method.
    }

    public function delete(Measure $measure): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM ' . self::TABLE_NAME .
        'WHERE id = :id'
        );

        $stmt->execute([
            'id' => $measure->getId()
        ]);

        //delete measure object ?
    }

    
}