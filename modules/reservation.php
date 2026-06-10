<?php

class Reservation
{
    private $database;
    /**
     * Summary of __construct
     * @param Database $database
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function listCities(): array
    {
        $sql = "SELECT DISTINCT COM_NOM FROM vik_commune ORDER BY COM_NOM ASC";
        $stmt = $this->database->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStepsOfLine($lineId): array
    {
        $sql = "SELECT com_code_insee, com_nom FROM ( SELECT com_code_insee,com_nom,MIN(ordre) ordre FROM ( SELECT co.com_code_insee,co.com_nom,no.noe_heure_passage AS ordre FROM vik_noeud no JOIN vik_commune co ON co.com_code_insee = no.com_code_insee_arret WHERE no.lig_num = :ligne UNION ALL SELECT co.com_code_insee,co.com_nom,no.noe_heure_passage + (no.noe_duree_prochain/1440) FROM vik_noeud no JOIN vik_commune co ON co.com_code_insee = no.com_code_insee_suivant WHERE no.lig_num = :ligne ) GROUP BY com_code_insee, com_nom ) ORDER BY ordre";
        $stmt = $this->database->prepareStatement($sql);
        $stmt->execute(['ligne' => $lineId . 'A']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
