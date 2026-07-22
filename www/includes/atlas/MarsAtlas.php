<?php
declare(strict_types=1);

final class MarsAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>95,'rating'=>2,'themes'=>['salute'=>100,'incidenti'=>90,'energia'=>80]],
            2 => ['priority'=>80,'rating'=>2,'themes'=>['denaro'=>80,'spese'=>90]],
            3 => ['priority'=>75,'rating'=>2,'themes'=>['spostamenti'=>90,'discussioni'=>80]],
            4 => ['priority'=>90,'rating'=>1,'themes'=>['casa'=>90,'famiglia'=>90,'tensioni'=>100]],
            5 => ['priority'=>80,'rating'=>2,'themes'=>['amore'=>70,'figli'=>80]],
            6 => ['priority'=>100,'rating'=>1,'themes'=>['salute'=>100,'lavoro'=>90,'stress'=>100]],
            7 => ['priority'=>100,'rating'=>1,'themes'=>['relazioni'=>100,'separazioni'=>90,'cause'=>80]],
            8 => ['priority'=>85,'rating'=>2,'themes'=>['prove'=>90,'trasformazione'=>80]],
            9 => ['priority'=>75,'rating'=>2,'themes'=>['viaggi'=>70,'estero'=>60]],
            10=> ['priority'=>95,'rating'=>2,'themes'=>['carriera'=>90,'conflitti'=>90]],
            11=> ['priority'=>70,'rating'=>2,'themes'=>['amicizie'=>70,'progetti'=>60]],
            12=> ['priority'=>100,'rating'=>1,'themes'=>['prove'=>100,'nemici'=>90,'salute'=>90]],

        ];
    }
}
