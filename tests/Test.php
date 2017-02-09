<?php
/**
 * Created by IntelliJ IDEA.
 * User: dylanaird
 * Date: 9/2/17
 * Time: 3:13 PM
 */

require '../vendor/autoload.php';

require_once '../src/GooglePlaces.php';
require_once '../src/GooglePlacesClient.php';


$google_places = new joshtronic\GooglePlaces('AIzaSyCBadIpHu7WgpO0IXGKZw143orleCgi_A4');

$google_places->query = "Shop 111 Epping Plaza High Street";
$results  = $google_places->textsearch();

highlight_string("<?php\n\$data =\n" . var_export($results, true) . ";\n?>");