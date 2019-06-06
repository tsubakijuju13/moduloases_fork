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
 * Estrategia ASES
 *
 * @author     Juan Pablo Moreno Muñoz
 * @author     Jeison Cardona Gómez
 * @package    block_ases
 * @copyright  2017 Juan Pablo Moreno Muñoz <moreno.juan@correounivalle.edu.co>
 * @copyright  2019 Jeison Cardona Gómez <jeison.cardona@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__). '/../../../../config.php');

 /**
 * Function that returns the current semester in a given interval
 * 
 * @see get_current_semester_byinterval($fecha_inicio,$fecha_fin)
 * @param $fecha_inicio ---> starting date
 * @param $fecha_fin ---> ending date
 * @return object that represents the semester within the given interval
 */

 function get_current_semester_byinterval($fecha_inicio,$fecha_fin){
     
     global $DB;

     $sql_query = "SELECT id  max, nombre FROM {talentospilos_semestre} WHERE fecha_inicio ='$fecha_inicio' and fecha_fin ='$fecha_fin' ";
     $current_semester = $DB->get_record_sql($sql_query);
     return $current_semester;
 }

 /**
 * Function that returns the current semester in a given approximate interval
 * @author Jeison Cardona Gómez <jeison.cardona@correounivalle.edu.co>
 * @param string $start_date With postgres fortmat YYYY-MM-DD
 * @param string $end_date With postgres fortmat YYYY-MM-DD
 * @return int $to_return id_semester
 */

function periods_management_get_current_semester_by_apprx_interval( $start_date, $end_date ){
     
    global $DB;

    $sql = "SELECT id 
                    FROM {talentospilos_semestre} 
                    WHERE fecha_inicio <= '$start_date' 
                    AND fecha_fin >= '$end_date'";

    $to_return = $DB->get_record_sql( $sql );

    if( $to_return ){
        return $to_return->id;
    }else{
        return null;
    }

}

/**
 * Function that returns the current semester
 * ## Fields returned
 * - max: rename for semestre.id
 * - semestre.nombre
 * @deprecated 5.3 No longer used because its return does not comply with the no modification policy, please @see periods_get_current_semester().
 * @return object that represents the current semester
 */
 
 function get_current_semester(){
     
     global $DB;

     $sql_query = "SELECT id AS max, nombre FROM {talentospilos_semestre} WHERE id = (SELECT MAX(id) FROM {talentospilos_semestre})";
     $current_semester = $DB->get_record_sql($sql_query);
     return $current_semester;
 }

 /**
 * Function that returns the current semester.
 * @author Jeison Cardona Gómez <jeison.cardona@correounivalle.edu.co>.
 * @return object that represents the current semester.
 * @return null if are no semesters registered.
 */
 
function periods_get_current_semester(){
     
    global $DB;

    $sql_query = "SELECT id, nombre, fecha_inicio, fecha_fin 
    FROM {talentospilos_semestre} 
    WHERE id = (
        SELECT MAX(id) 
        FROM {talentospilos_semestre}
    )";
    
    $current_semester = $DB->get_record_sql($sql_query);

    return $current_semester;
}

 /**
 * Function that returns the interval that represents a semester by its ID
 * 
 * @see get_semester_interval($id)
 * @param $id ---> semester's id
 * @return object that represents the semester 
 */
 
 function get_semester_interval($id){
     
     global $DB;

     $sql_query = "select * from mdl_talentospilos_semestre where id='$id'";
     $interval = $DB->get_record_sql($sql_query);
     return $interval;
 }


 /**
 * Function that returns all registered semesters
 * 
 * @see get_all_semesters()
 * @return array that contains every semester registered on the DataBase
 */

 function get_all_semesters(){
     global $DB;

     $sql_query = "SELECT id, nombre, fecha_inicio, fecha_fin FROM {talentospilos_semestre}";
     $all_semesters = $DB->get_records_sql($sql_query);          

     return $all_semesters;
     
 }

 //get_all_semesters();


 /**
 * Function that returns a semester given its id
 * 
 * @see get_semester_by_id($idSemester)
 * @param $idSemester -> semester's id
 * @return object that represents certain information about the specific semester
 */


 function get_semester_by_id($idSemester){
     global $DB;

     $sql_query = "SELECT nombre, fecha_inicio, fecha_fin FROM {talentospilos_semestre} WHERE id = '$idSemester'";
     $info_semester = $DB->get_record_sql($sql_query);
     setlocale(LC_TIME, "es_CO");     
     $info_semester->fecha_inicio = strftime("%d %B %Y", strtotime($info_semester->fecha_inicio));
     $info_semester->fecha_fin = strftime("%d %B %Y", strtotime($info_semester->fecha_fin));


     return $info_semester;


 }

 /**
 * Function which updates the information of a semester
 * 
 * @see update_semester($semesterInfo, $idSemester)
 * @param $semesterInfo -> array with the new information of a semester
 * @param $idSemester -> semester's id
 * @return boolean true if it was updated, false it wasn't
 */

 function update_semester($semesterInfo, $idSemester){

     global $DB;

     try{

          $semester = new stdClass();

          $semester->id = (int)$idSemester;
          $semester->nombre = $semesterInfo[1];
          $semester->fecha_inicio = $semesterInfo[2];
          $semester->fecha_fin = $semesterInfo[3];


          $update = $DB->update_record('talentospilos_semestre', $semester);


          return $update;
 
     }catch(Exception $e){
          return $e->getMessage();
     }


 }

 /**
 * Function that returns every semester, change its language and date-format to spanish 
 * 
 * @see get_all_semesters_table()
 * @return array
 */
 
 function get_all_semesters_table(){
     global $DB;

     $array_semesters = array();

     $sql_query = "SELECT id, nombre, fecha_inicio, fecha_fin FROM {talentospilos_semestre}";
     $all_semesters = $DB->get_records_sql($sql_query);

     setlocale(LC_TIME, "es_CO");

     $length_array = count($all_semesters);

     foreach ($all_semesters as $semester) {
          $all_semesters[$semester->id]->fecha_inicio = strftime("%d %B %Y", strtotime($all_semesters[$semester->id]->fecha_inicio));
          $all_semesters[$semester->id]->fecha_fin = strftime("%d %B %Y", strtotime($all_semesters[$semester->id]->fecha_fin));                             
     }

     foreach ($all_semesters as $semester) {
          array_push($array_semesters, $semester);
     }

     return $array_semesters;

 }
 const INVALID_SEMESTER_DATES = 0;
 const CANNOT_CREATE_IN_DATABASE = 0;

 /**
  * Function that validates a semester
  *
  * @param $name -> name of the semester
  * @param $beginning_date -> semester's starting date
  * @param $ending_date -> semester's ending date
  * @return boolean
  */
  
function validate_semester($name, $beginning_date_string, $ending_date_string, $semester_id) {
    
    $regex = "/^\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])$/";

    if($name == "" || $beginning_date_string == "" || $ending_date_string == ""){
        return "Debe llenar todos los campos";
    }
    else if(preg_match($regex,$beginning_date_string)==0){
        return "La fecha de inicio no sigue el patrón yyyy-mm-dd. Ejemplo: 2017-10-20";
    }
    else if(preg_match($regex,$ending_date_string)==0){
        return "La fecha de fin no sigue el patrón yyyy-mm-dd. Ejemplo: 2017-10-20";
    }
    else if(!$semester_id===undefined){
        if($semester_id == ""){
            return "Se encontró un problema con el identificador del semestre, vuelva a seleccionar el semestre";
        }
        else{
            return "success";
        }
    }
    else{
        $beginning_date = new Date($beginning_date_string);
        $endingDate = new Date($ending_date_string);
        
        if($beginning_date >= $endingDate){
            return "La fecha de inicio de semestre debe ser menor a la de finalización";
        }
        else{
            return "success";
        }
    }
    

    return true;
}

/**
 * Function which creates a new semester
 * 
 * @see create_semester($name, $beginning_date, $ending_date)
 * @param $name -> name of the semester
 * @param $beginning_date -> semester's starting date
 * @param $ending_date -> semester's ending date
 * @return number|array If an array is returned, there was an inconsistency on the validation,
 *                      otherwise returns a number (the ID of the new semester)
 */

 function create_semester($name, $beginning_date, $ending_date){

     global $DB;

     if(!validate_semester($name, $beginning_date, $ending_date)){
        return array('status_code'=> INVALID_SEMESTER_DATES,
                     'Las fechas de los semestres son invalidas',
                      null);
     }

     $newSemester = new stdClass;
     $newSemester->nombre = $name;
     $newSemester->fecha_inicio = $beginning_date;

     $newSemester->fecha_fin = $ending_date;

     $insert = $DB->insert_record('talentospilos_semestre', $newSemester, true);

     return $insert;


 }

 /**
  * Function that returns the semester id given its name
  * 
  * @see get_semester_id_by_name($semester_name)
  * @param $semester_name -> name of the semester to be found
  * @return Integer 
  */
 function get_semester_id_by_name($semester_name){

    global $DB;

    $sql_query = "SELECT id FROM {talentospilos_semestre} WHERE nombre = '$semester_name'";
    $result = $DB->get_record_sql($sql_query);

    if($result){

        $semester_id = $result->id;
        return $semester_id;

    }else{

        return false;

    }
}

 /**
 * Functions that returns all stored semesters.
 * @author Jeison Cardona Gómez <jeison.cardona@correounivalle.edu.co>
 * @return array
 */

function periods_management_get_all_semesters(){
     
    global $DB;
    $sql = "SELECT * FROM {talentospilos_semestre}";

    return $DB->get_records_sql( $sql );

}