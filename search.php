<?php
    if(isset($_GET['search'])){
        $toSearch = $_GET['search'];
    } else {
        $toSearch = "";
    }
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
    <link rel="stylesheet" href="./style/css/main.css">
    <link href='https://fonts.googleapis.com/css?family=Oswald&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src='libs/js/jquery.svg3dtagcloud.min.js'></script>
</head>
<body>
    <div class="row" >
        <div class="offset-md-3 offset-lg-3 col-lg-8 col-md-8 mb-5">
            <img src="./logo.PNG" alt="">
        </div>
    </div>


    <div class="row">
        <div class="offset-md-2 offset-lg-2 col-md-8 col-lg-8 input-group mb-3">
            <input class="form-control" id="search" type="text" name="search" placeholder="Votre recherche"
                    value=<?php
                        if(isset($toSearch)){
                            echo '"'.$toSearch.'"';
                        }
                    ?>>
            <div class="input-group-append">
                <button class="btn btn-primary" onclick="search();" type="button" id="button-addon2">Rechercher</button>
            </div>
        </div>
    </div>
    <div class="row offset-md-2 offset-lg-2 col-md-8 col-lg-8 input-group" id="searchResult">
        
    </div>    
</body>



<script
src="https://code.jquery.com/jquery-3.4.1.min.js"
integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
crossorigin="anonymous">
</script>
<script type="text/javascript">
    if($('#search').val() != null){
        search();
    }
    function search() {
        $('#searchResult').html("");
        $.ajax({
            url: '/tech_info_web/home.php',
            type: 'POST',
            dataType: "json",
            data: {
                search: $('#search').val(),
            },
            success: function(data){                
                console.log("DATAAAA", data, data == -1, data === -1);
                var strResult = "";
                
                if(data !== -1){
                    data.forEach((doc, ind) => {
                        console.log(doc.document.title);
                        var entries = [];
                        strResult = '<div class="row col-md-12 col-lg-12">';
                        strResult += '<div class="card custom-card col-md-12 col-lg-12">'
                        strResult += '<div class="custom-card-header row" id="heading' +ind+ '">';
                        strResult += '<h2 class="mb-0 col-md-11 col-lg-11 col-sm-11">';
                        strResult += '<button class="btn custom-btn" type="button" data-toggle="collapse" data-target="#collapse'+ ind +'" aria-expanded="true" aria-controls="collapseOne">';
                        strResult += doc.document.title;
                        strResult += '</button>'
                        strResult += '</h2>';
                        // strResult += '<div class="pull-right col-md-1 col-lg-1 col-sm-1 custom-tooltip cloud-div">';
                        // strResult += '<i class="fas fa-cloud cloud-icon"></i>';                        
                        // strResult += '</div>';
                        strResult += '</div>';
            
                        strResult += '<div id="collapse'+ ind +'" class="collapse" aria-labelledby="headingOne" >';
                        strResult += '<div class="card-body row">';
                        strResult += '<div class="col-md-6 col-lg-6 col-sm-6">';
                        strResult += '<a href="'+ doc.document.url +'" >'+doc.document.url+'</a></br>';
                        strResult += doc.document.desc;
                        strResult += '</div>';
                        strResult += '<div id="holder' + ind + '" class="col-md-6 col-lg-6 col-sm-6">';
                        strResult += '<i class="fas fa-5x fa-cloud cloud-icon"></i> : ';
                        strResult += '</div>';
                        strResult += '</div>';
                        strResult += '</div>';
                        strResult += '</div>';
                        strResult += '</div>'
                        $('#searchResult').append(strResult);
                        doc.keywords.forEach((keyword) => {
                            entries.push({label: keyword, url:'http://localhost/tech_info_web/search.php?search=' + keyword, target: '_top'});
                        });

                        var settings = {
                            entries: entries,
                            width: 350,
                            height: 350,
                            radius: '65%',
                            radiusMin: 75,
                            bgDraw: true,
                            bgColor: '#111',
                            opacityOver: 1.00,
                            opacityOut: 0.05,
                            opacitySpeed: 6,
                            fov: 800,
                            speed: 2,
                            fontFamily: 'Oswald, Arial, sans-serif',
                            fontSize: '15',
                            fontColor: '#fff',
                            fontWeight: 'normal',//bold
                            fontStyle: 'normal',//italic 
                            fontStretch: 'normal',//wider, narrower, ultra-condensed, extra-condensed, condensed, semi-condensed, semi-expanded, expanded, extra-expanded, ultra-expanded
                            fontToUpperCase: true,
                            tooltipFontFamily: 'Oswald, Arial, sans-serif',
                            tooltipFontSize: '11',
                            tooltipFontColor: '#fff',
                            tooltipFontWeight: 'normal',//bold
                            tooltipFontStyle: 'normal',//italic 
                            tooltipFontStretch: 'normal',//wider, narrower, ultra-condensed, extra-condensed, condensed, semi-condensed, semi-expanded, expanded, extra-expanded, ultra-expanded
                            tooltipFontToUpperCase: false,
                            tooltipTextAnchor: 'left',
                            tooltipDiffX: 0,
                            tooltipDiffY: 10
                        };
                        var holder = 'holder'+ind;
                        var svg3DTagCloud = new SVG3DTagCloud( document.getElementById(holder), settings );
                        $('svg').attr('class', 'cadre')
                        console.log(entries);
                    });
                    

                    /*var entries = [                
                        { label: 'Dev Blog', url: 'http://niklasknaack.blogspot.de/', target: '_top' },
                        { label: 'Flashforum', url: 'http://www.flashforum.de/', target: '_top' },
                        { label: 'jQueryScript.net', url: 'http://www.jqueryscript.net/', target: '_top' },
                        { label: 'Javascript-Forum', url: 'http://forum.jswelt.de/', target: '_top' },
                        { label: 'JSFiddle', url: 'https://jsfiddle.net/user/NiklasKnaack/fiddles/', target: '_top' },
                        { label: 'CodePen', url: 'http://codepen.io/', target: '_top' },
                        { label: 'three.js', url: 'http://threejs.org/', target: '_top' },
                        { label: 'WebGLStudio.js', url: 'http://webglstudio.org/', target: '_top' },
                        { label: 'JS Compress', url: 'http://jscompress.com/', target: '_top' },
                        { label: 'TinyPNG', url: 'https://tinypng.com/', target: '_top' },
                        { label: 'Can I Use', url: 'http://caniuse.com/', target: '_top' },
                        { label: 'URL shortener', url: 'https://goo.gl/', target: '_top' },
                        { label: 'HTML Encoder', url: 'http://www.opinionatedgeek.com/DotNet/Tools/HTMLEncode/Encode.aspx', target: '_top' },
                        { label: 'Twitter', url: 'https://twitter.com/niklaswebdev', target: '_top' },
                        { label: 'deviantART', url: 'http://nkunited.deviantart.com/', target: '_top' },
                        { label: 'Gulp', url: 'http://gulpjs.com/', target: '_top' },
                        { label: 'Browsersync', url: 'https://www.browsersync.io/', target: '_top' },
                        { label: 'GitHub', url: 'https://github.com/', target: '_top' },
                        { label: 'Shadertoy', url: 'https://www.shadertoy.com/', target: '_top' },
                        { label: 'Starling', url: 'http://gamua.com/starling/', target: '_top' },
                        { label: 'jsPerf', url: 'http://jsperf.com/', target: '_top' },
                        { label: 'Foundation', url: 'http://foundation.zurb.com/', target: '_top' },
                        { label: 'CreateJS', url: 'http://createjs.com/', target: '_top' },
                        { label: 'Velocity.js', url: 'http://julian.com/research/velocity/', target: '_top' },
                        { label: 'TweenLite', url: 'https://greensock.com/docs/#/HTML5/GSAP/TweenLite/', target: '_top' },
                        { label: 'jQuery', url: 'https://jquery.com/', target: '_top' },
                        { label: 'jQuery Rain', url: 'http://www.jqueryrain.com/', target: '_top' },
                        { label: 'jQuery Plugins', url: 'http://jquery-plugins.net/', target: '_top' },
                    ];
                    */
                   
                    // $( '#holder' ).svg3DTagCloud( settings );
                } else {
                    strResult = "<div class='col-lg-12 col-md-12'>";
                    strResult += "<div class='alert alert-info text-center' role='alert'>";
                    strResult += "Aucun résultat :("
                    strResult += "</div>";
                    strResult += "</div>"
                    $('#searchResult').append(strResult);
                }
            }
        })
        // .done(function(data){
        //         console.log(data);
        // })
        // .fail(function()  {
        //     alert("Sorry. Server unavailable. ");
        // });
    }
</script>
