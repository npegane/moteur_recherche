<?php
    const KEYWORD_MULTIPLIER = 5;
    const TITLE_MULTIPLIER = 6;
    function explodeBis($str){
        $regex = '/[^A-Za-z0-9 \'àéèçïìùòçêâî]/';
        $cleanStr = preg_replace($regex, '', $str);
        $cleanStr = mb_strtolower($cleanStr);
        $cleanStr = str_replace('?', '', $cleanStr);
        $tok = strtok($cleanStr, " \n\t");
        $tab_mot = [];
        $stopWords = [];
        $stopFile = fopen('./stopWords.txt', 'r');

        while(!feof($stopFile))  {
            $result = fgets($stopFile);
            $result = trim($result);
            array_push($stopWords, $result);
        }
        fclose($stopFile);

        while ($tok !== false) {
            $tok = strtok(" \n\t");
            // var_dump(!in_array($tok, $stopWords));
            // var_dump($tok);
            if(!in_array($tok, $stopWords)){
                array_push($tab_mot, $tok);
            }
        }
        return $tab_mot;
    }

    function getHtmlText($str){
        return strip_tags($str);
    }

    function get_title($chaine_html) {
        $modele = '#<title>(.+)</title>#isU';
        
		if (preg_match($modele, $chaine_html, $titre)){
            return $titre[1];
        }
		return false;
    }

    function strip_script($chaine_html){
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();

        $dom->loadHTML($chaine_html);

        $script = $dom->getElementsByTagName('script');

        $remove = [];

        foreach($script as $item)
        {
            $remove[] = $item;
        }

        foreach ($remove as $item)
        {
            $item->parentNode->removeChild($item); 
        }

        $html = $dom->saveHTML();
        return $html;
                
    }
    
    function get_meta_keywords($chaine_html) {
		$modele = '#<meta name="keywords" content="(.+)"#isU';

		if (preg_match($modele, $chaine_html, $titre)){
            $keywords = explode(',', $titre[1]);
            return $keywords;
        } 
		return false;
    }
    
    function get_meta_description($chaine_html) {
		$modele = '#<meta name="description" content="(.+)"#isU';

		if (preg_match($modele, $chaine_html, $description)){
            return $description[1];
        } 
		return false;
	}

    function getTitle($str){
        $matches = [];
        $regex = '/<title>.*<\/title>/';
        preg_match_all($regex, $str, $matches);
        $cleanStr = '';
        $regex2 = '/<title><\/title>/';
        for($i = 0 ; $i < count($matches[0]); $i++){
            // var_dump($matches[0]);
            // var_dump(preg_replace($regex, '', $matches[0][$i]));
            $cleanStr .= preg_replace($regex2, '', $matches[0][$i]);
        }
        return $cleanStr;
    }

    function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
    
    function processHTML($resource){
        $strFile = file_get_contents($resource);
        $strFile = mb_strtolower($strFile);
        // var_dump($strFile);
        // $strFile = remove_utf8_bom($strFile);
        $strFile = html_entity_decode($strFile, ENT_COMPAT, "ISO-8859-15");
        $title = get_title($strFile);
        $description = get_meta_description($strFile);
        
        if(!$description){
            $description = "no description";
        }
        $doc_id = addDoc($title, $resource, $description);
        $titleWords = explode(" ",$title);
        // var_dump($description);
        // var_dump($title);
        $almost_clean = strip_script($strFile);
        $d = new DOMDocument();
        $d->loadHTML($almost_clean);
        $bodyObj = $d->getElementsByTagName('body')->item(0);
        $body = $bodyObj->nodeValue;
        $words = explodeBis($body);

        $wordsCount = array_count_values($words);
        if($keywords = get_meta_keywords($strFile)){
            //var_dump($keywords);
            foreach($wordsCount as $word => $nbTimes)
            {
                if(in_array($word, $keywords)){
                    $wordsCount[$word] = $nbTimes * KEYWORD_MULTIPLIER;
                }
                if(in_array($word, $titleWords)){
                    $wordsCount[$word] = $nbTimes * TITLE_MULTIPLIER;
                }
            }
        }
        

        arsort($wordsCount);

        if(count($wordsCount) > 0){
            foreach($wordsCount as $word => $nbTimes)
            {
                
                $wordExists = getWord($word);
                // if($word == 'bruce'){
                //     var_dump("WORD get: \n");
                //     var_dump($word);
                //     var_dump($wordExists);
                // }
                if($wordExists == -1){
                    // var_dump(getWord($word));
                    addWord($word, $doc_id, $nbTimes);
                } else {
                    $word_id = $wordExists[0]['id'];
                    $word = $wordExists[0]['name'];
                    addOccurence($word_id, $doc_id, $nbTimes);
                }                
            }
        }
        
        // var_dump($wordsCount);
        return $wordsCount;
    }
    //A FAIRE
    //Nuage de Mot Clés
    //Correcteur d'orthographe
    

    function getDoc($url){
        $mysqli = new mysqli("localhost", "root", "", "db_indexation");
        
        if ($mysqli->connect_errno) {
            printf("Échec de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }
        

        //var_dump("QUERY: ".$url);
        $mysqli->query("SET NAMES UTF8;");
        if ($stmt = $mysqli->prepare("SELECT titre, description FROM Document WHERE url = ?")) {
            $stmt->bind_param("s", $url);
            $stmt->execute();
            $stmt->bind_result($doc_title, $doc_desc);
            $stmt->fetch();
            $stmt->close();
        }
        if(!$doc_title || !$doc_desc){
            $mysqli->close();
            return -1;
        }
        $doc_info = [
            "title" => $doc_title,
            "desc"  => $doc_desc
        ];
        $mysqli->close();
        return $doc_info;
        

    }

    function getDocByWord($word){
        $mysqli = new mysqli("localhost", "root", "", "db_indexation");
        $word = remove_utf8_bom($word);
        $word = strtolower($word);
        if ($mysqli->connect_errno) {
            printf("Échec de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }

        $mysqli->query("SET NAMES UTF8;");
        if ($stmt = $mysqli->prepare("SELECT id FROM Mot WHERE nom LIKE ?")) {
            $queryWord = $word."%";
            $stmt->bind_param("s", $queryWord);
            $stmt->execute();
            $stmt->bind_result($word_id);
            
            $stmt->fetch();
            // var_dump($word_id);
            $stmt->close();
            if(!$word_id){
                $mysqli->close();
                return -1;
            } else {
                $resultDocs = [];
                if ($stmt2 = $mysqli->prepare("SELECT Document.id, Document.titre, Document.url, Occurence.nb_occurence, Document.description FROM Document,Occurence 
                                                WHERE Occurence.id_mot = ?
                                                AND Occurence.id_document = Document.id
                                                ORDER BY Occurence.nb_occurence DESC")) {
                    $stmt2->bind_param("i", $word_id);
                    $stmt2->execute();
                    $stmt2->bind_result($doc_id, $doc_title, $doc_url, $nb_occs, $doc_desc);
                    while($stmt2->fetch()){
                        $doc_info = [
                            'id'        => $doc_id,
                            'title'     => $doc_title,
                            'url'       => $doc_url,
                            'nb_occs'   => $nb_occs,
                            'desc'      => $doc_desc
                        ];
                        array_push($resultDocs, $doc_info);
                    }
                    // getDocWords($doc_id);
                    $stmt2->close();
                }
            }
        }
        $mysqli->close();
        return $resultDocs;
        

    }

    function addDoc($title, $url, $description){
        $mysqli = new mysqli("localhost", "root", "", "db_indexation");

        if ($mysqli->connect_errno) {
            printf("Erreur lors de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }
        $mysqli->query("SET NAMES UTF8;");
        if ($stmt = $mysqli->prepare('INSERT INTO Document(titre, url, description) VALUES(?, ?, ?)')) {
            printf("Insertion du document '". $title ."'", $result->num_rows);
            $stmt->bind_param("sss", $title, $url, $description);
            $stmt->execute();
            $insert_id = $stmt->insert_id;
            $stmt->close();
        }

        // if ($result = $stmt->query("SELECT * FROM City", MYSQLI_USE_RESULT)) {
        //     if (!$mysqli->query("SET @a:='this will not work'")) {
        //         printf("Erreur : %s\n", $mysqli->error);
        //     }
        //     $result->close();
        // }
        $mysqli->close();
        return $insert_id;
    }

    function addWord($word, $doc_id, $nb_occ){
        $mysqli = new mysqli("p:localhost", "root", "", "db_indexation");

        if ($mysqli->connect_errno) {
            printf("Erreur lors de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }
        $mysqli->query("SET NAMES UTF8;");
        if ($mysqli->query("SET NAMES UTF8;") && $stmt = $mysqli->prepare('INSERT INTO Mot(nom) VALUES(?)')) {
            // printf("Insertion du Mot '". $word ."'");
            // if($name == 'bruce'){
            //     var_dump("WORD Results: \n");
            //     var_dump($word);
            // }
            $stmt->bind_param("s", $word);
            $stmt->execute();
            $word_id = $stmt->insert_id;
            $stmt->close();
            if ($stmt2 = $mysqli->prepare('INSERT INTO Occurence(id_mot, id_document, nb_occurence) VALUES(?, ?, ?)')) {
                $stmt2->bind_param("iii", $word_id, $doc_id, $nb_occ);
                $stmt2->execute();
                $stmt2->close();
            }
        }
        

        $mysqli->close();
    }

    function addOccurence($word_id, $doc_id, $nb_occ){
        $mysqli = new mysqli("localhost", "root", "", "db_indexation");

        if ($mysqli->connect_errno) {
            printf("Erreur lors de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }

        $mysqli->query("SET NAMES UTF8;");

        if ($mysqli->query("SET NAMES UTF8;") && $stmt = $mysqli->prepare('INSERT INTO Occurence(id_mot, id_document, nb_occurence) VALUES(?, ?, ?)')) {
            $stmt->bind_param("iii", $word_id, $doc_id, $nb_occ);
            $stmt->execute();
            $stmt->close();
        }

        $mysqli->close();
    }

    function getWord($name){
        $mysqli = new mysqli("p:localhost", "root", "", "db_indexation");
        $name = strtolower($name);
        if ($mysqli->connect_errno) {
            printf("Échec de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }

        $wordsResult = [];
        // if($name == 'bruce'){
        //     var_dump("NAME: \t".$name);
        // }
        //var_dump("QUERY: ".$url);
        $mysqli->query("SET NAMES UTF8;");
        if ($stmt = $mysqli->prepare("SELECT Mot.id, Mot.nom FROM Mot WHERE Mot.nom = ?")) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->bind_result($word_id, $nom);
            while($stmt->fetch()){
                $word_info = [
                    "id" => $word_id,
                    "name"  => $nom
                ];
                
                array_push($wordsResult, $word_info);
                // if($name == 'bruce'){
                //     var_dump("WORD Results: \n");
                //     var_dump($wordsResult);
                //     var_dump(count($wordsResult));
                // }
            }
            
            $stmt->close();
        }
        $mysqli->close();
        if(count($wordsResult) < 1){
            return -1;
        }
        
        
        return $wordsResult;
        

    }
    function getDocWords($doc_id){
        $mysqli = new mysqli("localhost", "root", "", "db_indexation");
        // var_dump($doc_id);
        
        if ($mysqli->connect_errno) {
            printf("Échec de la connexion : %s\n", $mysqli->connect_error);
            exit();
        }
        

        //var_dump("QUERY: ".$url);
        $mysqli->query("SET NAMES UTF8;");
        $words = [];
        if ($stmt = $mysqli->prepare("SELECT nom
                                      FROM Mot as M
                                      WHERE M.id IN 
                                        (
                                            SELECT O.id_mot FROM Occurence as O 
                                            WHERE O.id_document = ? 
                                            AND O.nb_occurence >15
                                        )")) {
            $stmt->bind_param("i", $doc_id);
            $stmt->execute();
            $stmt->bind_result($wordName);
            $stmt->fetch();
            
            while($stmt->fetch()){
                array_push($words, $wordName);
            }
            $stmt->close();
        }
        if(!$words){
            $mysqli->close();
            return -1;
        }
        
        $mysqli->close();
        return $words;
    }
?>

 