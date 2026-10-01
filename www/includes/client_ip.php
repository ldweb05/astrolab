<?php
/**
 * client_ip.php - IP del client per i limiti di login e registrazione.
 *
 * Senza configurazione restituisce REMOTE_ADDR (comportamento di sempre).
 * Sulla VPS, dietro un reverse proxy, indicare nel file .env l'indirizzo del
 * proxy in TRUSTED_PROXIES (piu' indirizzi separati da virgola): solo le
 * richieste che arrivano davvero da quel proxy useranno X-Forwarded-For, cosi'
 * nessuno puo' falsificare il proprio IP inviando l'header da solo.
 * Riferimento: docs/roadmaps/ROADMAP.md, "Limite per IP del login".
 */
function astrolab_client_ip(): string
{
    $remoto = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $fidati = array_filter(array_map('trim', explode(',', (string)getenv('TRUSTED_PROXIES'))));

    if ($fidati && in_array($remoto, $fidati, true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Si scorre la catena da destra: il primo indirizzo valido che non e' un
        // proxy fidato e' il client reale.
        $catena = array_reverse(array_map('trim', explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])));
        foreach ($catena as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) && !in_array($ip, $fidati, true)) {
                return substr($ip, 0, 64);
            }
        }
    }
    return substr($remoto, 0, 64);
}
