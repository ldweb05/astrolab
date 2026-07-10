<?php

if ($isAdmin) {
    $soggetti = $pdo->query(
        "SELECT id, nome, data_nascita, ora_nascita_gmt, latitudine, longitudine
         FROM soggetti ORDER BY nome"
    )->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare(
        "SELECT id, nome, data_nascita, ora_nascita_gmt, latitudine, longitudine
         FROM soggetti WHERE utente_id = ? ORDER BY nome"
    );
    $stmt->execute([$userId]);
    $soggetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$condizioni = [
    'Decima','Lavoro','Amore','Salute','Denaro','Denaro Low','Casa',
    '— Astri nelle Case —',
    '— Longitudine Cuspidi —',
];

$segni = [
    1=>'Ariete', 2=>'Toro', 3=>'Gemelli', 4=>'Cancro',
    5=>'Leone',  6=>'Vergine', 7=>'Bilancia', 8=>'Scorpione',
    9=>'Sagittario', 10=>'Capricorno', 11=>'Acquario', 12=>'Pesci',
];

$annoCorrente = (int)date('Y');
$soggettoId   = intval($_GET['id'] ?? $soggettoAttivo ?? 0);

if ($soggettoId > 0 && isset($_GET['id'])) {
    $auth->setSoggettoAttivo($soggettoId);
    $soggettoNome = $auth->getSoggettoNome();
}

$MACRO_AREE = [
    'europa'     => ['AD','AL','AT','BA','BE','BG','BY','CH','CY','CZ','DE','DK','EE','ES','FI','FR','GB','GR','HR','HU','IE','IS','IT','LI','LT','LU','LV','MC','MD','ME','MK','MT','NL','NO','PL','PT','RO','RS','RU','SE','SI','SK','SM','UA','VA','XK'],
    'nord_america'=> ['CA','US','MX','GL'],
    'centro_sud' => ['AG','AI','AR','AW','BB','BO','BR','BS','BZ','CL','CO','CR','CU','DM','DO','EC','FK','GD','GF','GP','GT','GY','HN','HT','JM','KN','KY','LC','MQ','MS','NI','PA','PE','PR','PY','SR','SV','TC','TT','UY','VC','VE','VG','VI'],
    'africa'     => ['AO','BF','BI','BJ','BW','CD','CF','CG','CI','CM','CV','DJ','DZ','EG','ER','ET','GA','GH','GM','GN','GQ','GW','KE','KM','LR','LS','LY','MA','MG','ML','MR','MU','MW','MZ','NA','NE','NG','RW','SC','SD','SL','SN','SO','SS','ST','SZ','TD','TG','TN','TZ','UG','ZA','ZM','ZW'],
    'medio_oriente'=>['AE','BH','IL','IQ','IR','JO','KW','LB','OM','PS','QA','SA','SY','TR','YE'],
    'asia'       => ['AF','AM','AZ','BD','BN','BT','CN','GE','HK','ID','IN','JP','KG','KH','KP','KR','KZ','LA','LK','MN','MO','MM','MV','MY','NP','PH','PK','SG','TH','TJ','TL','TM','TW','UZ','VN'],
    'oceania'    => ['AU','FJ','FM','GU','KI','MH','MP','NC','NR','NZ','PF','PG','PW','SB','TO','TV','UY','VC','VE','VG','VI'],
];