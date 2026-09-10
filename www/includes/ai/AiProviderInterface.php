<?php
/**
 * Contratto minimo per un provider AI Agent in ASTROLAB.
 *
 * Ogni implementazione deve:
 * - non lanciare mai eccezioni non gestite verso il chiamante;
 * - restituire sempre un array con le chiavi 'ok', 'testo', 'errore';
 * - non modificare mai direttamente dati ASTROLAB (RSM, RL, Regole, veti, ecc.);
 * - limitarsi a comprendere/generare testo e, in futuro, a scegliere quali
 *   tool ASTROLAB chiamare - mai eseguire calcoli astronomici o astrologici
 *   in autonomia.
 */
interface AiProviderInterface
{
    /**
     * Invia un prompt testuale al modello e restituisce la risposta.
     *
     * Non deve mai lanciare un'eccezione: qualunque errore (rete, timeout,
     * quota, risposta malformata, chiave assente) va intercettato
     * internamente e restituito nel campo 'errore'.
     *
     * @param string $prompt Testo del prompt da inviare al modello.
     * @return array{ok: bool, testo: string|null, errore: string|null}
     */
    public function chiedi(string $prompt): array;
}
