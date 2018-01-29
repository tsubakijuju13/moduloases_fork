<?php 
    require_once(dirname(__FILE__). '/../../../../config.php');

    function dphpforms_generate_html_recorder($id_form, $rol_, $student_id, $id_monitor){

        global $DB;

        $FORM_ID = $id_form;
        $ROL = $rol_;

        $html = null;
        
        $sql = '
        
            SELECT * FROM {talentospilos_df_tipo_campo} AS TC 
            INNER JOIN (
                SELECT * FROM {talentospilos_df_preguntas} AS P 
                INNER JOIN (
                    SELECT *, F.id AS mod_id_formulario, FP.id AS mod_id_formulario_pregunta FROM {talentospilos_df_formularios} AS F
                    INNER JOIN {talentospilos_df_form_preg} AS FP
                    ON F.id = FP.id_formulario WHERE F.id = '.$FORM_ID.'
                    ) AS AA ON P.id = AA.id_pregunta
                ) AS AAA
            ON TC.id = AAA.tipo_campo
            ORDER BY posicion
        
        ';

        $result = $DB->get_records_sql($sql);
        $result = (array) $result;
        $result = array_values($result);

        $row = $result[0];
        $form_name = $row->{'nombre'};
        $form_name_formatted = strtolower($form_name);
        $form_name_formatted = str_replace(" ", "_", $form_name_formatted);
        $form_name_formatted = str_replace("   ", "_", $form_name_formatted);
        $form_name_formatted = str_replace(' ', "_", $form_name_formatted);
        $form_name_formatted = str_replace("á", "a", $form_name_formatted);
        $form_name_formatted = str_replace("é", "e", $form_name_formatted);
        $form_name_formatted = str_replace("í", "i", $form_name_formatted);
        $form_name_formatted = str_replace("ó", "o", $form_name_formatted);
        $form_name_formatted = str_replace("ú", "u", $form_name_formatted);
        $form_name_formatted = str_replace("ü", "u", $form_name_formatted);
        $form_name_formatted = str_replace("ñ", "n", $form_name_formatted);
        $form_name_formatted = utf8_encode($form_name_formatted);
        $form_name_formatted = $form_name_formatted . "_" . $row->{'mod_id_formulario'};


        $html = $html .  '<form id="'. $form_name_formatted .'" method="'. $row->{'method'} .'" action="'. $row->{'action'} .'" class="dphpforms col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-bottom:0.7em">' ;
        $html = $html .  '<h1>'.$form_name.'</h1><hr style="border-color:red;">';
        $html = $html .  '<input name="id" value="'.$row->{'mod_id_formulario'}.'" style="display:none;">';
        $html = $html .  '<input name="id_monitor" value="'.$id_monitor.'" style="display:none;">';//Pendiente para eliminación
        $html = $html .  '<input name="id_estudiante" value="'.$student_id.'" style="display:none;">';//Pendiente para eliminación
        
        for($i = 0; $i < count($result); $i++){
            $row = null;
            $row = $result[$i];

            $campo = $row->{'campo'};
            $enunciado = $row->{'enunciado'};
            
            $atributos = json_decode($row->{'atributos_campo'});

            //Consulta de permisos
            $sql_permisos = '
                SELECT * FROM {talentospilos_df_per_form_pr} WHERE id_formulario_pregunta = '.$row->{'id_pregunta'}.'
            ';
            
            $result_permisos = $DB->get_record_sql($sql_permisos);

            $permisos = $result_permisos;
            $permisos_JSON = json_decode($permisos->permisos);
            
            foreach ($permisos_JSON as $key => $v_rol) {

                
                //$permisos_JSON = null;
                if($v_rol->{'rol'} == $ROL){

                    $lectura = false;
                    $escritura = false;

                    foreach ($v_rol->{'permisos'} as $key2 => $value) {
                        if($value == "lectura"){
                            $lectura = true;
                        }
                        if($value == "escritura"){
                            $escritura = true;
                        }

                    }

                    if($lectura){

                        $enabled = null;
                        if(!$escritura){
                            $enabled = "disabled";
                        }

                        $field_attr_class = '';
                        $field_attr_type = '';
                        $field_attr_placeholder = '';
                        $field_attr_maxlength = '';

                        if(property_exists($atributos, 'class')){
                            $field_attr_class = $atributos->{'class'};
                        }

                        if(property_exists($atributos, 'type')){
                            $field_attr_type = $atributos->{'type'};
                        }

                        if(property_exists($atributos, 'placeholder')){
                            $field_attr_placeholder = $atributos->{'placeholder'};
                        }

                        if(property_exists($atributos, 'maxlength')){
                            $field_attr_maxlength = $atributos->{'maxlength'};
                        }

                        if($campo == 'TEXTFIELD'){
                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >' . $enunciado . ':<br>';
                            $html = $html .  ' <input id="'.$row->{'mod_id_formulario_pregunta'}.'" class="form-control" type="'.$field_attr_type.'" placeholder="'.$field_attr_placeholder.'" name="'.$row->{'mod_id_formulario_pregunta'}.'" maxlength="'.$field_attr_maxlength.'" '.$enabled.'><br>' . "\n";
                            $html = $html .  '</div>';
                        }

                        if($campo == 'TEXTAREA'){
                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >' . $enunciado . ':<br>';
                            $html = $html .  ' <textarea id="'.$row->{'mod_id_formulario_pregunta'}.'" class="form-control" name="'. $row->{'mod_id_formulario_pregunta'} .'" maxlength="'.$field_attr_maxlength.'" '.$enabled.'></textarea><br>' . "\n";
                            $html = $html .  '</div>';
                        }

                        if($campo == 'DATE'){
                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >' . $enunciado . ':<br>';
                            ' <input id="'.$row->{'mod_id_formulario_pregunta'}.'" class="form-control" type="date" name="'.$row->{'mod_id_formulario_pregunta'}.'" '.$enabled.'><br>' . "\n";
                            '</div>';
                        }
                        
                        if($campo == 'DATETIME'){
                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >' . $enunciado . ':<br>';
                            $html = $html .  ' <input id="'.$row->{'mod_id_formulario_pregunta'}.'" class="form-control" type="datetime-local" name="'.$row->{'mod_id_formulario_pregunta'}.'" '.$enabled.'><br>' . "\n";
                            $html = $html .  '</div>';
                        }

                        if($campo == 'TIME'){
                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >' . $enunciado . ':<br>';
                            $html = $html .  ' <input id="'.$row->{'mod_id_formulario_pregunta'}.'" class="form-control" type="time" name="'.$row->{'mod_id_formulario_pregunta'}.'" '.$enabled.'><br>' . "\n";
                            $html = $html .  '</div>';
                        }

                        if($campo == 'RADIOBUTTON'){
                            $opciones = json_decode($row->{'opciones_campo'});
                            $array_opciones = (array)$opciones;
                            $number_opciones = count($array_opciones);

                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >';
                            $html = $html .  '<input type="hidden" name="'.$row->{'mod_id_formulario_pregunta'}.'" value="-#$%-">
                                              <label id="'.$row->{'mod_id_formulario_pregunta'}.'">'.$enunciado.'</label>';
                            $html = $html .  '<div class="opcionesRadio" style="margin-bottom:0.4em">';
                            for($x = 0; $x < $number_opciones; $x++){
                                $opcion = (array) $array_opciones[$x];
                                $html = $html .  '
                                    <div id="'.$row->{'mod_id_formulario_pregunta'}.'" name="'.$row->{'mod_id_formulario_pregunta'}.'" class="radio">
                                        <label><input type="radio" name="'.$row->{'mod_id_formulario_pregunta'}.'" value="'.$opcion['valor'].'" name="optradio" '.$enabled.'>'.$opcion['enunciado'].'</label>
                                    </div>
                                ' . "\n";
                            }
                            $html = $html .  '<a href="javascript:void(0);" class="limpiar btn btn-xs btn-default" >Limpiar</a>
                                </div>
                             </div>
                            ' . "\n";
                        }

                        if($campo == 'CHECKBOX'){
                            $opciones = json_decode($row->{'opciones_campo'});
                            $array_opciones = (array)$opciones;
                            $number_opciones = count($array_opciones);

                            $html = $html .  '<div class="div-'.$row->{'mod_id_formulario_pregunta'}.' '.$field_attr_class.'" >';
                            $html = $html .  '
                            <label>'.$enunciado.'</label>';
                            for($x = 0; $x < $number_opciones; $x++){
                                $opcion = (array) $array_opciones[$x];
                                $html = $html .  '
                                    <div class="checkbox">
                                        <input type="hidden" name="'.$row->{'mod_id_formulario_pregunta'}.'" value="-1">
                                        <label><input type="checkbox" name="'.$row->{'mod_id_formulario_pregunta'}.'" value="'.$opcion['valor'].'" '.$enabled.'>'.$opcion['enunciado'].'</label>
                                    </div>
                                </div>
                                ' . "\n";
                            }

                        }

                    }

                    break;

                }
            }

        }
        $html = $html .  ' <hr style="border-color:red"><button type="submit" class="btn btn-sm btn-default">Registrar</button>' . "\n";
        $html = $html .  ' </form>' . "\n";

        return $html;

    }
   
?>