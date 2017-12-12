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
 * Talentos Pilos
 *
 * @author     Esteban Aguirre Martinez
 * @package    block_ases
 * @copyright  2017 Esteban Aguirre Martinez <estebanaguirre1997@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('../managers/pilos_tracking/tracking_functions.php');
require_once('../managers/instance_management/instance_lib.php');
require_once ('../managers/permissions_management/permissions_lib.php');
require_once ('../managers/validate_profile_action.php');
require_once ('../managers/menu_options.php');


include('../lib.php');
include("../classes/output/renderer.php");
include("../classes/output/report_trackings_page.php");

global $PAGE, $USER;

$title = "estudiantes";
$pagetitle = $title;
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('instanceid', PARAM_INT);

require_login($courseid, false);


//se oculta si la instancia ya está registrada
if(!consult_instance($blockid)){
    header("Location: instance_configuration.php?courseid=$courseid&instanceid=$blockid");
}


$contextcourse = context_course::instance($courseid);
$contextblock =  context_block::instance($blockid);
$PAGE->set_context($contextcourse);


$url = new moodle_url("/blocks/ases/view/report_tackings.php",array('courseid' => $courseid, 'instanceid' => $blockid));


//Navigation setup
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = navigation_node::create($title,$url, null, 'block', $blockid);
$coursenode->add_node($blocknode);
$blocknode->make_active();

//se crean los elementos del menu
$menu_option = create_menu_options($USER->id, $blockid, $courseid);

// Crea una clase con la información que se llevará al template.
$data = 'data';
$data = new stdClass;

// Evalua si el rol del usuario tiene permisos en esta view.
$actions = authenticate_user_view($USER->id, $blockid);
$data = $actions;
$data->menu = $menu_option;



//Se obtiene el rol del usuario que se encuentra conectado, username y su correo electronico respectivo.

$userrole = get_id_rol($USER->id,$blockid);
$usernamerole= get_name_rol($userrole);
$username = $USER->username;
$email = $USER->email;

$seguimientotable ="";
$globalArregloPares = [];
$globalArregloGrupal =[];
$table="";
$table_periods="";

$periods = get_semesters();

//obtiene el intervalo de fechas del ultimo semestre
$intervalo_fechas[0] = reset($periods)->fecha_inicio;
$intervalo_fechas[1] =reset($periods)->fecha_fin;
$intervalo_fechas[2] =reset($periods)->id;


//organiza el select de periodos.
$table_periods.=get_period_select($periods);

if($usernamerole=='monitor_ps'){


    //Se recupera los estudiantes de un monitor en la instancia y se organiza el array que será transformado en el toogle.
    $seguimientos = monitorUser($globalArregloPares,$globalArregloGrupal,$USER->id,0,$blockid,$userrole,$intervalo_fechas);
    $table.=has_tracking($seguimientos);

}elseif($usernamerole=='practicante_ps'){


    
    //Se recupera los estudiantes de un practicante en la instancia y se organiza el array que será transformado en el toogle.
    $seguimientos =practicanteUser($globalArregloPares,$globalArregloGrupal,$USER->id,$blockid,$userrole,$intervalo_fechas);
    $table.=has_tracking($seguimientos);

}elseif($usernamerole=='profesional_ps'){


    
    //Se recupera los estudiantes de un profesional en la instancia y se organiza el array que será transformado en el toogle.
    $seguimientos = profesionalUser($globalArregloPares,$globalArregloGrupal,$USER->id,$blockid,$userrole,$intervalo_fechas);
    $table.=has_tracking($seguimientos);

}elseif($usernamerole=='sistemas' or $username == "administrador" or $username == "sistemas1008" or $username == "Administrador"){

    //Obtiene los periodos existentes y los roles que contengan "_ps".
    $roles = get_rol_ps();

    //Obtiene las personas que se encuentran en el último semestre añadido y cuyos roles terminen en "_ps.
    $people = get_people_onsemester(reset($periods)->id,$roles,$blockid);


    //organiza el select de personas.
    $table_periods.=get_people_select($people);

}
$table_permissions=show_according_permissions($table,$actions);

$data->table_periods =$table_periods;
$data->table=$table_permissions;

$PAGE->requires->css('/blocks/ases/style/styles_pilos.css', true);
$PAGE->requires->css('/blocks/ases/style/bootstrap_pilos.css', true);
$PAGE->requires->css('/blocks/ases/style/datepicker.css', true);
$PAGE->requires->css('/blocks/ases/style/bootstrap_pilos.min.css', true);
$PAGE->requires->css('/blocks/ases/style/sweetalert.css', true);
$PAGE->requires->css('/blocks/ases/style/round-about_pilos.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.foundation.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.foundation.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.jqueryui.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.jqueryui.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/jquery.dataTables.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/jquery.dataTables.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/jquery.dataTables_themeroller.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.tableTools.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/NewCSSExport/buttons.dataTables.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.tableTools.css', true);
$PAGE->requires->css('/blocks/ases/style/sweetalert.css', true);
$PAGE->requires->css('/blocks/ases/js/select2/css/select2.css', true);
$PAGE->requires->css('/blocks/ases/style/side_menu_style.css', true);
$PAGE->requires->js_call_amd('block_ases/pilos_tracking_main','init');
$PAGE->set_url($url);
$PAGE->set_title($title);

$output = $PAGE->get_renderer('block_ases');

echo $output->header();
$report_trackings_page = new \block_ases\output\report_trackings_page($data);
echo $output->render($report_trackings_page);
echo $output->footer();