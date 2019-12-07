
<?php
    include_once('./functions.php');
    set_time_limit(500);
    if(isset($_POST['search'])){
        $toSearch = $_POST['search'];
    } else {
        $toSearch = "bruce";
    }

    // $words = processHTML('https://www.lemonde.fr/climat/article/2019/11/24/intemperies-l-alerte-rouge-levee-dans-le-var-et-les-alpes-maritimes_6020315_1652612.html');
    $docs_to_index = [
        "https://fr.wikipedia.org/wiki/Café",
        "https://fr.wikipedia.org/wiki/Chromosome",
        "https://fr.wikipedia.org/wiki/Mitose",
        "https://fr.wikipedia.org/wiki/Venezuela",
        "https://fr.wikipedia.org/wiki/Colombie",
        "https://fr.wikipedia.org/wiki/Christophe_Colomb",
        "https://fr.wikipedia.org/wiki/Christian_Bale",
        "https://www.20minutes.fr/arts-stars/cinema/2657627-20191124-video-christian-bale-explique-pourquoi-dit-non-quatrieme-batman",
        "https://www.lesinrocks.com/2017/02/15/musique/musique/triplego-a-base-rap-spleen/",
        "https://yard.media/triplego-interview-machakil-2019/",
        "https://www.abcdrduson.com/interviews/triplego-machakil/",
        "https://www.futura-sciences.com/tech/definitions/informatique-intelligence-artificielle-555/",
        "https://www.vanityfair.fr/culture/people/articles/la-malediction-de-bruce-lee/30087",
        "https://www.lepoint.fr/cinema/bruce-lee-un-danseur-de-cha-cha-cha-devenu-roi-du-kung-fu-23-10-2019-2343044_35.php",
        "https://fr.wikipedia.org/wiki/Paris",
        "https://fr.wikipedia.org/wiki/Chocolat",
        "https://fr.wikipedia.org/wiki/Bruce_Willis",
        "https://fr.wikipedia.org/wiki/Bruce_Lee",
        "https://fr.wikipedia.org/wiki/Russie",
        'https://www.bienmanger.com/1L64_Miel.html',
        'https://www.passeportsante.net/fr/Nutrition/EncyclopedieAliments/Fiche.aspx?doc=gingembre_nu',     
    ];
    foreach($docs_to_index as $url)
    {
        $result = getDoc($url);
        if($result == -1){
            processHTML($url);
        } else {
            // return json_encode(["Response" => "Aucun Résultat"]);
        }
    }
    if($toSearch){
        $docs = getDocByWord($toSearch);
        // var_dump($docs);
        if($docs != -1){
            $response = [];
            foreach($docs as $doc){
                $keywords = getDocWords($doc['id']);
                array_push($response, [
                    'document' => $doc,
                    'keywords' => $keywords
                ]);

            }
        } else {
            $response = -1;
        }
        
        
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
?>