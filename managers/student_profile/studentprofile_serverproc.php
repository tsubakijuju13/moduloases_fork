<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Ases block
 *
 * @author     Iader E. García Gómez
 * @package    block_ases
 * @copyright  2018 Iader E. García <iadergg@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__). '/../../../../config.php');
require_once('../lib/student_lib.php');
require_once('../student_profile/studentprofile_lib.php');

date_default_timezone_set('America/Bogota');

$msg_error = new stdClass();
$msg = new stdClass();

if(isset($_POST['func'])){

    if($_POST['func'] == 'update_status_ases') {
        $result_save_dropout = 1;

        if (isset($_POST['current_status']) && isset($_POST['new_status']) && isset($_POST['instance_id']) && isset($_POST['code_student'])) {
            if (isset($_POST['id_reason_dropout']) && isset($_POST['observation'])) {
                if ($_POST['id_reason_dropout'] . trim() != "" && $_POST['observation'] . trim() != "") {
                    $result_save_dropout = save_reason_dropout_ases($_POST['code_student'], $_POST['id_reason_dropout'], $_POST['observation']);
                    $result = update_status_ases($_POST['current_status'], $_POST['new_status'], $_POST['instance_id'], $_POST['code_student'], $_POST['id_reason_dropout']);
                } else {
                    $result = 0;
                    $result_save_dropout = 0;
                }
            } else {
                $result = update_status_ases($_POST['current_status'], $_POST['new_status'], $_POST['instance_id'], $_POST['code_student']);
            }

            $msg = new stdClass();

            if ($result && $result_save_dropout) {
                $msg->title = 'Éxito';
                $msg->msg = 'Estado actualizado con éxito.';
                $msg->status = 'success';
                $msg->previous_status = $_POST['current_status'];

            } else {
                $msg->title = "Error";
                $msg->msg = "Error al realizar registro.";
                $msg->status = 'error';
                if (!$result_save_retiro) {
                    $msg->msg .= "\nNo se guarda motivo de retiro en la base de datos.";
                }
                if (!$result) {
                    $msg->msg .= "\nNo se guarda estado en la base de datos.";

                }
                if (!$result && !$result_save_dropout) {
                    $msg->msg .= "\nCampos obligatorios no enviados.";
                }
            }

            echo json_encode($msg);
        } else {
            $msg_error = new stdClass();
            $msg_error->title = 'Error';
            $msg_error->msg = 'Error al conectarse con el servidor.';
            $msg_error->status = 'error';

            echo json_encode($msg_error);
        }
    }elseif($_POST['func'] == 'save_tracking_peer'){
        save_tracking_peer_proc();
    }elseif($_POST['func'] == 'delete_tracking_peer' && isset($_POST['id_tracking'])){
        $id_tracking_peer = $_POST['id_tracking'];
        delete_tracking_peer_proc($id_tracking_peer);
    }else if($_POST['func'] == 'send_email'){
        send_email($_POST["risk_array"], $_POST["observations_array"],'' ,$_POST["id_student_moodle"], $_POST["id_student_pilos"], $_POST["date"],'', '', $_POST["url"]);
    }else if($_POST['func'] == 'update_tracking_status'){
        $id_ases_student = $_POST['id_ases_student'];
        $id_academic_program = $_POST['id_academic_program'];
        $result = update_tracking_status($id_ases_student, $id_academic_program);

        if($result){
            $msg =  new stdClass();
            $msg->title = "Éxito";
            $msg->msg = "El campo se ha actualizado con éxito";
            $msg->status = "success";
            echo json_encode($msg);
        } else {
            $msg =  new stdClass();
            $msg->title = "Error";
            $msg->msg = "Error al actualizar el campo";
            $msg->status = "error";
            echo json_encode($msg);
        }
    } else if($_POST['func'] == 'update_user_image'){
        $new_user_image = $_FILES['new_image_file'];
        $user_id = $_POST['mdl_user_id'];
        $user = new stdClass();
        $user->id = $user_id;
        update_user_image_profile($user->id, 0);
        print_r($user_id);
    }
}

/**
 * Updates 'estado Ases' field on {talentospilos_usuario} table
 *
 * @see save_status_ases_proc($new_status, $id_ases, $id_reason = null, $observations=null)
 * @param $new_status --> New status to save on 'estado Ases' field
 * @param $id_ses --> ASES student id
 * @param $id_reason = null --> Retirement reason id
 * @param $observations = null --> observations to save
 * @return object in a json format
 */

function save_status_ases_proc($new_status, $id_ases, $id_reason = null, $observations=null){

    $result = save_status_ases($new_status, $id_ases, $id_reason, $observations);

    echo json_encode($result);
}

function loadMotivos(){
    $result = getMotivosRetiros();
    $msg = new stdClass();
    $msg->size = count($result);
    $msg->data = $result;
    echo json_encode($msg);
}

function loadMotivoRetiroStudent(){
    if(isset($_POST['talentosid']))
    {
        echo json_encode(getMotivoRetiroEstudiante($_POST['talentosid']));

    }else{
        $msg =  new stdClass();
        $msg->error = "Error :(";
        $msg->msg = "Error al comunicarse con el servidor. No se encuentran las variables necesarias para cargar el motivo retiro";
        echo json_encode($msg);
    }
}

/**
 * Validates if a form is totally complete
 *
 * @see validate_form_tracking_peer()
 * @return string --> validation result
 */
function validate_form_tracking_peer(){
    if(!isset($_POST['date'])){
        return "El campo FECHA no llegó al servidor.";
    }else if(!isset($_POST['place'])){
        return "El campo LUGAR no llegó al servidor.";
    }else if(!isset($_POST['h_ini'])){
        return "El campo HORA INICIAL no llegó al servidor.";
    }else if(!isset($_POST['m_ini'])){
        return "El campo MINUTO INICIAL no llegó al servidor.";
    }else if(!isset($_POST['h_fin'])){
        return "El campo HORA FINALIZACIÓN no llegó al servidor.";
    }else if(!isset($_POST['m_fin'])){
        return "El campo MINUTO FINALIZACIÓN no llegó al servidor.";
    }else if(!isset($_POST['tema'])){
        return "El campo TEMA no llegó al servidor.";
    }else if(!isset($_POST['objetivos'])){
        return "El campo OBJETIVOS no llegó al servidor.";
    }else if(!isset($_POST['individual'])){
        return "El campo ACT. INDIVIDUAL no llegó al servidor.";
    }else if(!isset($_POST['riesgo_ind'])){
        return "El campo RIESGO INDIVIDUAL no llegó al servidor.";
    }else if(!isset($_POST['familiar'])){
        return "El campo ACT. FAMILIAR no llegó al servidor.";
    }else if(!isset($_POST['riesgo_familiar'])){
        return "El campo RIESGO FAMILIAR no llegó al servidor.";
    }else if(!isset($_POST['academico'])){
        return "El campo ACT. ACADÉMICO no llegó al servidor.";
    }else if(!isset($_POST['riesgo_aca'])){
        return "El campo RIESGO ACADÉMICO no llegó al servidor.";
    }else if(!isset($_POST['economico'])){
        return "El campo ACT. ECONÓMICO no llegó al servidor.";
    }else if(!isset($_POST['riesgo_econom'])){
        return "El campo RIESGO ECONÓMICO no llegó al servidor.";
    }else if(!isset($_POST['vida_uni'])){
        return "El campo ACT. VIDA UNIVERSITARIA Y CIUDAD no llegó al servidor.";
    }else if(!isset($_POST['riesgo_uni'])){
        return "El campo RIESGO VIDA UNIVERSITARIA Y CIUDAD no llegó al servidor.";
    }else if(!isset($_POST['id_ases'])){
        return "El campo ID ESTUDIANTE ASES no llegó al servidor.";
    }else if(!isset($_POST['id_instance'])){
        return "El campo ID INSTANCIA BLOQUE no llegó al servidor.";
    }else if(!isset($_POST['observaciones'])){
        return "El campo OBSERVACIONES no llegó al servidor.";
    }else if(!isset($_POST['id_tracking_peer'])){
        return "El campo ID SEGUIMIENTO no llegó al servidor.";
    }else{
        return "success";
    }
}

/**
 * Saves and validated the form on database
 *
 * @see save_tracking_peer_proc()
 * @return string --> validation result
 */
function save_tracking_peer_proc(){

    global $USER;

    $result_msg = new stdClass();
    $is_valid = validate_form_tracking_peer();

    if($is_valid == "success"){

        $date = new DateTime();
        $date->getTimestamp();

        $tracking_object = new stdClass();
        $tracking_object->id = (int)$_POST['id_tracking_peer'];
        $tracking_object->id_monitor = $USER->id;
        $tracking_object->created = time();
        $tracking_object->fecha = strtotime($_POST['date']);
        $tracking_object->lugar = $_POST['place'];
        $tracking_object->hora_ini = $_POST['h_ini'].":".$_POST['m_ini'];
        $tracking_object->hora_fin = $_POST['h_fin'].":".$_POST['m_fin'];
        $tracking_object->tema = $_POST['tema'];
        $tracking_object->objetivos = $_POST['objetivos'];
        $tracking_object->tipo = "PARES";
        $tracking_object->status = 1;
        $tracking_object->individual = $_POST['individual'];
        $tracking_object->individual_riesgo = $_POST['riesgo_ind'];
        $tracking_object->familiar_desc = $_POST['familiar'];
        $tracking_object->familiar_riesgo = $_POST['riesgo_familiar'];
        $tracking_object->academico = $_POST['academico'];
        $tracking_object->academico_riesgo = $_POST['riesgo_aca'];
        $tracking_object->economico = $_POST['economico'];
        $tracking_object->economico_riesgo = $_POST['riesgo_econom'];
        $tracking_object->vida_uni = $_POST['vida_uni'];
        $tracking_object->vida_uni_riesgo = $_POST['riesgo_uni'];
        $tracking_object->id_estudiante_ases = $_POST['id_ases'];
        $tracking_object->id_instancia = $_POST['id_instance'];
        $tracking_object->revisado_profesional = 0;
        $tracking_object->revisado_practicante = 0;
        $tracking_object->observaciones = $_POST['observaciones'];

        $result_saving = save_tracking_peer($tracking_object);

        echo json_encode($result_saving);

    }else{
        $result_msg->title = "Error";
        $result_msg->msg = $is_valid;
        $result_msg->type = "error";

        echo json_encode($result_msg);
    }
}
