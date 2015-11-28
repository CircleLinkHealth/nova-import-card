<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CPM API</title>

    <link href="https://api-test.careplanmanager.com/css/app.css" rel="stylesheet">
    <link href="https://api-test.careplanmanager.com/css/lavish.css" rel="stylesheet">
    <link href="https://api-test.careplanmanager.com/img/favicon.png" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Scripts -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://api-test.careplanmanager.com/js/scripts.js"></script>
    <script src="https://api-test.careplanmanager.com/js/bootstrap-select.min.js"></script>

    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
</head>
<body>
<h1>CCD Viewer Lite (Meds Only)</h1>
<pre>
<form enctype="multipart/form-data" id="yourregularuploadformId" action="#" method="post">
    <input type="file" id="file" name="file" multiple="multiple">
    <input type="submit" class="btn btn-default" value="Upload & View CCD" name="submit">
</form>
    <?php

    /***
     * SimpleXMLElement simplexml_load_file ( string $filename [, string $class_name = "SimpleXMLElement" [, int $options = 0 [, string $ns = "" [, bool $is_prefix = false ]]]] )
     *
     */
    echo "<h3>File: ". $_FILES['file']['name'] . "</h3>";
    $file = $_FILES['file']['tmp_name'];

    if (file_exists($file)) {
        $xml = simplexml_load_file($file);
        $patientData = ((array)$xml->recordTarget->patientRole->id);
        $patientMRN = $patientData['@attributes']['extension'];
        echo "MRN: ". $patientMRN;
        echo "<BR><BR>";
        $med = ((array)$xml->component[0]->structuredBody[0]->component[4]->section[0]->text->table->tbody);

        // var_export($med01[0]); echo "<BR>First One<br><br>";
        $arrMedsTR = $med['tr'];
        foreach ($arrMedsTR as $key => $value) {
            $arrMedsTD = (array)$value->td;
            if ($arrMedsTD[5] != 'Active') continue;
            $medList = "". $arrMedsTD[0];
            if (strlen($arrMedsTD[2]) > 4) $medListIns = "; ". $arrMedsTD[2];
            $arrMeds = $arrMedsTD[1];
            // var_dump($arrMedsTD);
            if ($arrMeds != 'Unknown') {
                $respRXNORMtmp = explode(" ", $arrMedsTD[0]);
                $urlRXNORM = "https://rxnav.nlm.nih.gov/REST/rxclass/class/byDrugName.json?drugName=" . $respRXNORMtmp[0];
                $url = "https://api.fda.gov/drug/event.json?api_key=Vz5LHfnB7lnwBjkE5CPj7AlkwGxu72IjGrJ2cvMT&search=".$arrMeds;
                $response = file_get_contents($url);
                $responseRXNORM = file_get_contents($urlRXNORM);
                $resp = json_decode($response, true);
// var_export($resp);
                $respRXNORM = json_decode($responseRXNORM, true);
                $drugRXNORM = $respRXNORM['rxclassDrugInfoList']['rxclassDrugInfo'][0]['rxclassMinConceptItem']['className'];
// With NDC get SET ID
                $drugNDC = $resp['results'][0]['patient']['drug'][0]['openfda']['package_ndc'][0];
                $urlSETID = "http://dailymed.nlm.nih.gov/dailymed/services/v1/ndc/$drugNDC/spls.json";
                $responseSETID = file_get_contents($urlSETID);
                $respSETID = json_decode($responseSETID, true);
// With SET ID get Data from
                $z=0;
                do {
                    $drugSETID = $resp['results'][0]['patient']['drug'][$z]['openfda']['spl_set_id'][0];
                    $z++;
                } while ( $drugSETID == NULL && $z < 10);
                $urlDailyMed = "http://dailymed.nlm.nih.gov/dailymed/services/v2/spls/$drugSETID.xml";

                $drugUsageNeat = '';
                if ($resp['results'][0]['patient']['drug'][0]['medicinalproduct'] != NULL) {
                    $drugUsageNeat .= 'BrandName: '.$resp['results'][0]['patient']['drug'][0]['medicinalproduct'] . "\n";
                }
                $drugUsageNeat .= "Other Name: ". $arrMeds ."\n";
                $drugUsageNeat .= "RXNorm Class: ". $drugRXNORM ."\n";

                foreach ($resp['results'][0]['patient']['drug'] as $drugID => $drugList) {
                    if ($drugList['drugindication'] != NULL) {
                        $drugUsageNeat .= "Drug Indication: " . $drugList['drugindication'] . "\n";
                    }

                }
                if ($resp['results'][0]['patient']['drug'][0]['openfda']['pharm_class_epc'][0] != NULL) {
                    $drugUsageNeat .= "Pharm Class: " . $resp['results'][0]['patient']['drug'][0]['openfda']['pharm_class_epc'][0] . "\n";
                }
                $drugUsageNeat .= "NDC: " . $drugNDC;

                echo "- <a target='_Blank' href='$url' title='$drugUsageNeat'>$medList</a>$medListIns";
                // echo " <a target='_BLANK' href='http://dailymed.nlm.nih.gov/dailymed/services/v1/ndc/$drugNDC/spls.json'>NDC: $drugNDC</a> ";
                if ($drugSETID) echo "<a target='_BLANK' href='https://dailymed.nlm.nih.gov/dailymed/drugInfo.cfm?setid=$drugSETID'><img src='https://dailymed.nlm.nih.gov/dailymed/images/logo.png' height='15px'></a>";
                if($drugUsageNeat <> '')	echo "";
                // var_export($drugSETID);
                echo "</br>";
            }
            // echo "$key <BR>";
            // foreach ($arrMedsTD as $key => $value) {if (!is_array($value) ) echo " -$key-($value) <BR>";		}
            // echo "<BR>";
        }
        echo "</pre><BR><BR>";
        echo "<h3>Under Dev Section...</h3>";
        echo "<pre>";
        // var_export($respSETID['DATA'][0][0]);
        // foreach ($arrMeds as $key => $value) {
        // 	if ($value != 'Unknown') {
        // 			$url = "https://api.fda.gov/drug/event.json?api_key=Vz5LHfnB7lnwBjkE5CPj7AlkwGxu72IjGrJ2cvMT&search=$value";
        // 			$response = file_get_contents($url);
        // 			$resp = json_decode($response, true);

        // 		$drugUsageNeat = '';
        // 		foreach ($resp['results'][0]['patient']['drug'] as $drugID => $drugList) {
        // 			if ($drugList['drugindication'] != NULL) {
        // 					$drugUsageNeat .= $drugList['drugindication'] . "\n";
        // 			}

        // 		}
        // 			if ($resp['results'][0]['patient']['drug'][0]['openfda']['pharm_class_epc'][0] != NULL) {
        // 					$drugUsageNeat .= $resp['results'][0]['patient']['drug'][0]['openfda']['pharm_class_epc'][0] . "\n";
        // 			}

        // 			echo "<a target='_Blank' href='https://api.fda.gov/drug/event.json?api_key=Vz5LHfnB7lnwBjkE5CPj7AlkwGxu72IjGrJ2cvMT&search=$value' title='$drugUsageNeat'>$value</a>";
        // 			if($drugUsageNeat <> '')	echo "*";
        // 			echo "</br>";
        // 	}
        // }
        echo "</pre>";
        // var_export($drugUsage);
        // var_export($resp['results'][0]['patient']['drug']);


    } else {
        if ($_POST['file']) {
            $errMsg ='Failed to open test.xml.';
        } else {
            $errMsg ='Upload an XML CCDA Record.';
        }
        exit($errMsg);
    }
    ?>
</pre>
</body>
</html>