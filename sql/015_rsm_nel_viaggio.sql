-- 015_rsm_nel_viaggio.sql
-- Diario RSM, estensione del 02-10-2026 (docs/roadmaps/ROADMAP_DIARIO_RSM.md, D21-D23).
-- Il viaggio contiene da se' i dati della sua RSM, cosi' le sessioni RS si possono
-- cancellare: le coordinate del luogo esistono gia' (latitudine, longitudine); si
-- aggiungono l'anno della RSM e, per i viaggi scritti dall'astrologo, il soggetto
-- della RSM da cui prendere il cielo natale.

BEGIN;

SET search_path = public;

ALTER TABLE viaggi_rsm
    ADD COLUMN anno_rsm SMALLINT,
    ADD COLUMN soggetto_rsm_id INTEGER REFERENCES soggetti(id) ON DELETE SET NULL,
    ADD CONSTRAINT viaggi_rsm_anno_rsm_check CHECK (anno_rsm IS NULL OR anno_rsm BETWEEN 1900 AND 2100),
    -- il soggetto della RSM serve solo ai viaggi dell'astrologo; quelli del soggetto
    -- usano gia' soggetto_id
    ADD CONSTRAINT viaggi_rsm_soggetto_rsm_check CHECK (soggetto_rsm_id IS NULL OR utente_id IS NOT NULL);

CREATE INDEX idx_viaggi_rsm_soggetto_rsm ON viaggi_rsm (soggetto_rsm_id);

COMMIT;
