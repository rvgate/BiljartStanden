<?php
include('lib/simple_html_dom.php');

$query = $_GET['query'];


function findCompetities($matches){
    $competities = [];
    $url = 'http://biljartpoint.nl/index.php';
    $query = [
        'page' => 'organisaties',
    ];
    $html = file_get_html($url . '?' . http_build_query($query));
    $links = $html->find('a');
    foreach($links as $link) {
        $page = $link->href;
        $organisatie = trim($link->innertext);
        if (in_array($organisatie, $matches)) {
            $html = file_get_html('http://biljartpoint.nl/' . $page);
            $tables = $html->find('table');
            foreach($tables as $table) {
                $hr = $table->find('th');
                $header = ucfirst($hr[1]->innertext);
                $rows = $table->find('tr');
                foreach($rows as $row) {
                    $columns =  $row->find('td');
                    $code = $columns[0]->innertext;
                    if($code != NULL && $code != "&nbsp;") {
                        $link = $columns[1]->find('a')[0];
                        $results = [
                            'organisatie' => $organisatie,
                            'header' => $header,
                            'code' => $code,
                            'competitie' => $link->innertext,
                            'href' => $link->href,
                            'stand' => parseStanding($link->href),
                        ];
                        if($results['stand']) {
                            $competities[]=$results;
                        }
                    }
                }
            }
        }
    }
    return $competities;
}

function replaceSpecials($input){
    return str_replace([
        "&nbsp;",
        "&#039;",
        " -",
        "- -",
    ],[
        "",
        "'",
        " - ",
        "-"
    ], $input);
}

function parseStanding($page) {
    $rows = [];
    if(strpos($page, "poule=on") !== false){
        return false;
    }
    $meta_columns = [];
    $html = file_get_html('http://biljartpoint.nl/' . $page);
    $tables = $html->find('#content > table > table');
    if($tables){
        $table = $tables[0];
        $ths = $table->find('th');
        if($ths) {
            foreach ($ths as $th) {
                $value = replaceSpecials(ucfirst($th->innertext));
                $meta_columns[] = $value;
            }
        }
        $trs = $table->find('tr');
        if($trs) {
            foreach($trs as $tr) {
                $tds = $tr->find('td');
                if($tds){
                    $row = [];
                    foreach($tds as $td) {
                        $row[] = replaceSpecials(trim(strip_tags($td->innertext)));
                    }
                    $rows[] = $row;
                }
            }
        }
    }

    $encoded = json_encode($rows);
    if(strpos($encoded, "Almere") === false){
        return false;
    }
    return [
        "columns" => $meta_columns,
        "rows" => $rows,
    ];
}

echo json_encode(findCompetities(['KNBB Nationale competities', 'KNBB District Eem- en Flevoland']));