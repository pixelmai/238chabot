<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
include __DIR__ . '/../db_helper.php';
/**
 * @Issues:
 * - Add another button for load more
 */

$endpoint = 'http://www.recipepuppy.com/api/?';
$sizePerRequest = 3;
$defaultThumbnail = "http://arifbakery-patisserie.co.uk/wp-content/themes/nevia/images/shop-01.jpg";

function getRecipe($viand, $extra_context=null, $top=0) {
    global $sizePerRequest;
    // check previous db log
    $page = 1;
    $top = 0;
    if ($extra_context) {
        $log = getUserDataForCommand($extra_context["user_id"], "recipe");
        if ($log && $log->recipe ." ". $viand) {
            $prevContext = (json_decode($log["context"], true));
            $prevContext = $prevContext["context"];
            $page = $prevContext["page"];
            $top = $prevContext["top"] + $prevContext["responseCount"];
            if ($top % $sizePerRequest != 0) {
                // go to next page
                $page += 1;
                $top = 0;
            }
        }
    }

    var_dump($viand);
    var_dump($page);
    $resp = queryApi($viand, $page);
    $elements = formatElements(
        array_slice($resp['results'], $top, $top + $sizePerRequest));

    // log results to db
    if ($extra_context) {
        saveSessionData($extra_context["user_id"],
            "recipe", $viand, [
                "response" => $elements,
                "context" => [
                    "top" => $top,
                    "responseCount" => count($elements),
                    "page" => $page
                ]
            ]);
    }
    return formatAnswer($elements);
}

function queryApi($keyword, $page=1) {
    global $endpoint;
    $payload = array(
        'q' => $keyword,
        'p' => $page
    );
    $url = $endpoint . http_build_query($payload);
    $resp = json_decode(file_get_contents($url), true);
    return $resp;
}

function hasMore($keyword) {
    return count(queryApi($keyword)['results']) > 0;
}

function formatElements($results) {
    global $defaultThumbnail;
    $elements = array();
    foreach ($results as $key => $value) {
        $element = [
            'title' => $value['title'],
            'item_url' => $value['href'],
            'image_url' => $value['thumbnail'] ?: $defaultThumbnail,
            'subtitle' => $value['ingredients']
        ];
        if ($value['href']) {
            $element['buttons'] = [[
                'type' => 'web_url',
                'url' => $value['href'],
                'title' => 'Learn more'
            ]];
        }
        $elements[] = $element;
    }
    return $elements;
}

function formatAnswer($elements) {
    $answer = ["attachment" => [
        "type"    => "template",
        "payload" => [
            "template_type" => "list",
            "elements"      => $elements
        ],
    ]];
    return $answer;
}

// Example:
// getRecipe('adobo', ['user_id' => "1234"]);
// call as many times to test pagination
