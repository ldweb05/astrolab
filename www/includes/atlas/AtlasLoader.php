<?php
declare(strict_types=1);

require_once __DIR__.'/RsmAtlas.php';
require_once __DIR__.'/ThemeCatalog.php';

require_once __DIR__.'/SunAtlas.php';
require_once __DIR__.'/MoonAtlas.php';
require_once __DIR__.'/MercuryAtlas.php';
require_once __DIR__.'/VenusAtlas.php';
require_once __DIR__.'/MarsAtlas.php';
require_once __DIR__.'/JupiterAtlas.php';
require_once __DIR__.'/SaturnAtlas.php';
require_once __DIR__.'/UranusAtlas.php';
require_once __DIR__.'/NeptuneAtlas.php';
require_once __DIR__.'/PlutoAtlas.php';

final class AtlasLoader
{
    public static function load(): array
    {
        return [

            'asc'       => RsmAtlas::ascendenteRadix(),

            'sole'      => SunAtlas::houses(),
            'luna'      => MoonAtlas::houses(),
            'mercurio'  => MercuryAtlas::houses(),
            'venere'    => VenusAtlas::houses(),
            'marte'     => MarsAtlas::houses(),
            'giove'     => JupiterAtlas::houses(),
            'saturno'   => SaturnAtlas::houses(),
            'urano'     => UranusAtlas::houses(),
            'nettuno'   => NeptuneAtlas::houses(),
            'plutone'   => PlutoAtlas::houses(),

            'themes'    => ThemeCatalog::THEMES,

        ];
    }
}
