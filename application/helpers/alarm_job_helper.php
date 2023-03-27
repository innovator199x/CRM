<?php


function get_alarm_job_type_name($ajt_id) {
    switch ($ajt_id) {
        case 1:
            return "Test & Tag Appliances";
            break;
        case 2:
            return "Smoke Alarms";
            break;
        case 3:
            return "Safety Switch - View";
            break;
        case 4:
            return "Safety Switch - Mechanical Test";
            break;
        case 5:
            return "Safety Switch";
            break;
        case 6:
            return "Corded Windows";
            break;
        case 7:
            return "Water Meter";
            break;
        case 8:
            return "Smoke Alarm & Safety Switch";
            break;
        case 9:
            return "Bundle SA.CW.SS";
            break;
        case 10:
            return "";
            break;
        case 11:
            return "Smoke Alarm & Water Meter";
            break;
        case 12:
            return "Smoke Alarms (IC)";
            break;
        case 13:
            return "Smoke Alarm & Safety Switch (IC)";
            break;
        case 14:
            return "Bundle SA.CW.SS (IC)";
            break;
        case 15:
            return "Water Efficiency";
            break;
        case 16:
            return "Smoke Alarms & Water Efficiency";
            break;
        case 17:
            return "Bundle SA.SS.WE";
            break;
        case 18:
            return "Bundle SA.SS.CW.WE";
            break;
        default:
            return "Description not found";
    }
}


?>