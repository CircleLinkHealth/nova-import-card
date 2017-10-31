<?php namespace App\Services;

use App\CPRulesPCP;
use App\User;
use DB;
use Illuminate\Support\Facades\Facade;

class CareplanUIService extends Facade
{


    /**
     * Render Careplan Sections
     *
     * @param array $pcpSections
     * @param $programId
     * @param $wpUser
     * @return string
     */
    public function renderCareplanSections($pcpSections = array(), $programId, User $user = null)
    {
        // if not set, get all pcp sections
        if (!$pcpSections) {
            $pcpSections = array();
            $pcps = CPRulesPCP::where('prov_id', '=', $programId)->where('status', '=', 'Active')->get();
            if (count($pcps) > 0) {
                foreach ($pcps as $pcp) {
                    $pcpSections[] = $pcp->section_text;
                }
            }
        }
        // render each section
        $pcpSectionHtml = '';
        if (!empty($pcpSections)) {
            foreach ($pcpSections as $pcpSection) {
                $pcpSectionHtml .= $this->renderCareplanSection($pcpSection, $programId, $user);
            }
        }
        return $pcpSectionHtml;
    }

    /**
     * Render Careplan Section
     *
     * @param $pcpSectionText
     * @param $programId
     * @param $wpUser
     * @return string
     */
    public function renderCareplanSection($pcpSectionText, $programId, User $user = null)
    {
        // start content
        $content = '';

        $user_id = '0';
        if ($user) {
            $user_id = $user->id;
        }

        $sectionData = $this->getCareplanSectionData($programId, $pcpSectionText, $user);

        //dd($sub_meta);
        if (empty($sectionData)) {
            return false;
        }

        $items = $sectionData['items'];
        $sub_meta = $sectionData['sub_meta'];

        foreach ($items as $item => $arrItem_value) {
            $content .= '<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
<h4>' . $item . '</h4>';
            $content .= "<input type='hidden' name='meta_type' value='ucp'>";
            $content .= "<input type='hidden' name='uid' value='$user_id'>";
            $n = 0;
            $itn = 0;
            foreach ($arrItem_value as $subKey => $arrSubKeyVal) {
                // column math
                $num_items_total = count($arrItem_value);
                $num_items_per_column = round($num_items_total / 2);

                // output column containers if needed
                if ($n == 0) {
                    $content .= '<div class="form-block form-block--left col-md-6">
								<div class="row">';
                } else if ($n == $num_items_per_column) {
                    $content .= '<div class="form-block form-block--right col-md-6">
								<div class="row">';
                }

                // opening html for each specific item
                $content .= '<div class="monitor-item monitor-hypertension">
											<div class="form-group">
												<div class="form-item col-sm-12">
													<div class="checkbox text-medium-big">';

                // item checkbox, active/inactive, + corresponding details button for modal
                $item_id = 000;
                $item_parent_id = 000;
                $item_display_detail = 0;
                $item_status = 'Inactive';
                if (isset($sub_meta[$item][$subKey])) {
                    // has children items
                    $item_id = $sub_meta[$item][$subKey][key($sub_meta[$item][$subKey])]['items_id'];
                    // has detail button for children item(s), toggle display from meta key ui_show_detail
                    $item_display_detail = 1;
                    if (isset($sub_meta[$item][$subKey][key($sub_meta[$item][$subKey])]['ui_show_detail'])) {
                        $item_display_detail = $sub_meta[$item][$subKey][key($sub_meta[$item][$subKey])]['ui_show_detail'];
                    }
                    $item_parent_id = $sub_meta[$item][$subKey][key($sub_meta[$item][$subKey])]['items_parent'];
                    $item_pcp_id = $sub_meta[$item][$subKey][key($sub_meta[$item][$subKey])]['pcp_id'];
                    $item_status = $arrSubKeyVal['status'];
                    //echo "<br>lastrow<pre>";var_export($arrSubKeyVal);echo "</pre><br>";
                    $item_ui_sort = $arrSubKeyVal['ui_sort'];
                } else if (isset($sub_meta[$item][0][$subKey]['items_id'])) {
                    // singleton, has no children
                    $item_id = $sub_meta[$item][0][$subKey]['items_id'];
                    $item_parent_id = $sub_meta[$item][0][$subKey]['items_id'];
                    $item_status = $sub_meta[$item][0][$subKey]['item_status'];
                    $item_pcp_id = $sub_meta[$item][0][$subKey]['pcp_id'];
                    $item_ui_sort = $sub_meta[$item][0][$subKey]['ui_sort'];
                }

                // these vars are all used to toggle item related UI stuff
                $item_checkbox_key = 'CHECK_STATUS|' . $item_parent_id . '|' . $item_id . "|status";
                $modal_key = $item_id . '_modal';
                $modal_contentclone_id = $item_id . '_modal_contentclone';
                $is_checked = '';
                $detail_button_style = 'display:none;';
                $is_collapse = 'collapse';
                $modal_contentclone_extra = ' aria-expanded="false" class="collapse"';
                if ($item_status == 'Active') {
                    $is_checked = ' checked="checked"';
                    $detail_button_style = '';
                    $is_collapse = 'collapse';
                    $modal_contentclone_extra = ' aria-expanded="true" class="collapse in"';
                }

                // text for button, either Instructions or Details
                $detail_button_text = 'Details';
                if (($item_pcp_id == 1) || ($item_pcp_id == 9)
                    || $item == 'Diagnosis / Problems to Monitor'
                ) {
                    $detail_button_text = 'Instructions';
                }

                // main item label
                $main_item_label = $subKey;
                // main item checkbox / label
                $content .= '<div class="radio-inline"><input type="hidden" name="' . $item_checkbox_key . '" value="Inactive">';
                $content .= '<input type="checkbox" id="' . preg_replace('/\s+/', '', $subKey) . '" name="' . $item_checkbox_key . '" value="Active" ' . $is_checked . ' class="itemTrigger" data-toggle="' . $is_collapse . '" data-target="#' . $modal_contentclone_id . '"><label for="' . preg_replace('/\s+/', '', $subKey) . '"><span> </span>' . $main_item_label . '</label></div>';

                // detail(information) button
                if ($item_display_detail == 1) {
                    $content .= '<button type="button" class="btn btn-default btn-xs btn-monitor text-right" data-toggle="modal" id="' . preg_replace('/\s+/', '', $subKey) . 'Detail" style="' . $detail_button_style . '" data-target="#' . $modal_key . 'Modal" >' . $detail_button_text . '</button>';
                }


                //var_dump($sub_meta[$item]);
                $modal_content = "";
                $row_opened = false;
                //var_dump($sub_meta[$item][$subKey]);
                if (isset($sub_meta[$item][$subKey])) {
                    foreach ($sub_meta[$item][$subKey] as $key => $value) {
                        // process track_as_observations
                        if (isset($value['track_as_observation'])) {
                            // insert hidden form element
                            $modal_content .= "<input type='hidden' name='" . $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value_track_as_observation' value='".$value['track_as_observation']."'>\n<BR>";
                        }
                        $inputVal = $value['value'];
                        // meta key to start row
                        if ($value['ui_row_start'] == 1) {
                            $modal_content .= "<div class='row'>";
                        }
                        // meta key to start column (value is column size)
                        if ($value['ui_col_start']) {
                            $modal_content .= "<div class='col-md-".$value['ui_col_start']."'>";
                        }
                        // $modal_content .= "&nbsp; ";
                        switch ($value['ui_fld_type']) {
                            case 'SELECT':
                                $modal_content .= $key . " ";
                                $arrOptions = explode(',', $value['ui_fld_select']);
                                $size = count($arrOptions);
                                $modal_content .= "<SELECT name='" . $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value[]' size=$size  style='height: 100%;' multiple>\n<BR>";
                                foreach ($arrOptions as $key => $optValue) {
                                    $selected = '';
                                    if (strpos($inputVal, $optValue) !== false) {
                                        $selected = " SELECTED";
                                    }
                                    $content .= "<option value=$optValue $selected>$optValue</option>\n";
                                }
                                $modal_content .= "</SELECT>" . $value['items_id'] . "<br>\n";
                                break;

                            case 'INPUT':
                                $modal_content .= $key . " ";
                                $modal_content .= "<input type='text' name='" . $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value' value='$inputVal' placeholder='" . $value['ui_placeholder'] . "'>\n";
                                break;

                            case 'RADIO_MULT':
                                $modal_content .= $key . " ";
                                $modal_content .= '<div class="checkbox text-medium-big">';
                                for ($i = 1; $i < 8; $i++) {
                                    if ($i == 1) {
                                        $day = 'M';
                                    }
                                    if ($i == 2) {
                                        $day = 'T';
                                    }
                                    if ($i == 3) {
                                        $day = 'W';
                                    }
                                    if ($i == 4) {
                                        $day = 'T';
                                    }
                                    if ($i == 5) {
                                        $day = 'F';
                                    }
                                    if ($i == 6) {
                                        $day = 'S';
                                    }
                                    if ($i == 7) {
                                        $day = 'S';
                                    }
                                    $status = '';
                                    if (strpos($inputVal, strval($i)) > -1) {
                                        $status = ' checked="checked"';
                                    }
                                    // $modal_content .= '<input type="checkbox" name="test" id="" value="1">';
                                    $name = $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value";
                                    $modal_content .= '<div class="radio-inline"><input type="checkbox" id="' . $name . $i . '" name="' . $name . '[]" value="' . $i . '" ' . $status . '><label for="' . $name . $i . '"><span> </span>&nbsp;' . $day . '</label></div>';
                                    // $modal_content .= "<input type='checkbox' name='". $value['ui_fld_type']."|".$value['items_id'] ."|".$value['ucp_id'] ."|value[]' id='". $value['ui_fld_type']."|".$value['items_id'] ."|".$value['ucp_id'] ."|value[]' ".$status ." value='$i'><label for='". $value['ui_fld_type']."|".$value['items_id'] ."|".$value['ucp_id'] ."|value$i'>$i</label> \n";
                                }
                                $modal_content .= "</div>";
                                break;

                            case 'RADIO':
                                $radio_options = array();
                                if (!empty($value['radio_options'])) {
                                    $radio_options = json_decode($value['radio_options'], 1);
                                }
                                $modal_content .= $key . " ";
                                $name = $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value";
                                $modal_content .= '<div class="radio">';
                                $i = 1;
                                foreach ($radio_options as $label => $option_value) {
                                    $selected = '';
                                    if ($value['value'] == $option_value) {
                                        $selected = 'checked="checked"';
                                    }
                                    $modal_content .= '<input type="radio" '.$selected.' id="'.$name.$i.'" name="'.$name.'" value="'.$option_value.'"><label for="'.$name.$i.'"><span> </span>'.$label.'</label>&nbsp;&nbsp;';
                                    $i++;
                                }
                                $modal_content .= '</div>';
                                break;

                            case 'CHECK':
                                //echo "<pre>";var_dump($value);echo "</pre>";
                                $modal_content .= $key . " ";
                                $checkboxfix_name = $value['ui_fld_type'] . "FIX|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value|ALT";
                                $checkbox_name = $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value";
                                $modal_content .= '<div class="radsio-inline">';
                                $modal_content .= '<input type="hidden" name="' . $checkboxfix_name . '" ' . $inputVal . ">\n";
                                // $modal_content .= '<input type="checkbox" id="' . $checkbox_name . '" name="' . $checkbox_name . '" ' . $inputVal . '><label for="' . $checkbox_name . '"><span> </span>&nbsp;&nbsp;' . $key . '</label>';
                                $modal_content .= '<input type="checkbox" id="' . $checkbox_name . '" name="' . $checkbox_name . '" ' . $inputVal . '><label for="' . $checkbox_name . '"><span>&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;</label>';
                                $modal_content .= '</div><br />';
                                break;

                            case 'TEXTAREA':
                                $modal_content .= "<TEXTAREA name='" . $value['ui_fld_type'] . "|" . $value['items_id'] . "|" . $value['ucp_id'] . "|value' >$inputVal</TEXTAREA>\n<BR>";
                                break;

                            default:
                                # code...
                                break;
                        }
                        // meta key to end column
                        if (!empty($value['ui_col_end']) && $value['ui_col_end'] == 1) {
                            $modal_content .= "</div>";
                        }
                        // meta key to end row
                        if (!empty($value['ui_col_end']) && $value['ui_row_end'] == 1) {
                            $modal_content .= "</div>";
                        }
                        // item inc ++
                        $itn++;
                    }
                }
                //echo "<pre>";var_dump($item_display_detail);echo "</pre>";
                // only display modal if detail button is to be shown
                if ($item_display_detail == 0) {
                    $content .= '
                <div id="' . $modal_contentclone_id . '" ' . $modal_contentclone_extra . '><div class="form-group modal-box-clone" modal-key="' . $modal_key . '" id="' . $modal_key . 'BoxClone">
			        	' . $modal_content . '
					</div></div>';
                }
                if ($n==($num_items_per_column-1)) {
                    $content .= '</div></div>';
                } else if ($n == ($num_items_total-1)) {
                    $content .= '</div></div>';
                }
                $content .= "</div></div></div></div>";

                // only display modal if detail button is to be shown
                if ($item_display_detail == 1) {
                    $content .= '
                    <!-- Modal -->
            <div class="modal modal--monitor fade" id="' . $modal_key . 'Modal" tabindex="-1" role="dialog" aria-labelledby="' . $modal_key . 'ModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="' . $modal_key . 'ModalLabel"><span class="text-sans-serif text-thin">' . $detail_button_text . ':</span> ' . $subKey . '</h4>
                  </div>
                  <div class="modal-body">
                        <div class="form-group" id="' . $modal_key . 'Box">
                            ' . $modal_content . '
                        </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" >SAVE</button>
                  </div>
                </div>
              </div>
            </div><!-- /modal /#' . str_replace(' ', '', strtolower($subKey)) . 'Modal -->';
                }

                $n++;
            }
            $content .= "</div>";
            $content .= "<BR>";
        }

        //dd($content);
        return $content;
    }

    public function getCareplanSectionData(
        $programId,
        $pcpSectionText,
        $user
    ) {

        $id = $programId;
        $section_text = $pcpSectionText;
        $user_id = '0';
        if ($user) {
            $user_id = $user->id;
        }

        // build sql string
        $sql_ucp = "select p.*, i.*, its.meta_value 'item_status', si.items_text 'sub_parent', u.ucp_id, u.user_id, u.meta_key, u.meta_value
,imf.meta_value 'ui_fld_type',im_ro.meta_value 'radio_options', ims.meta_value 'ui_sort', imv.meta_value 'ui_fld_select', imd.meta_value 'ui_default', im_usd.meta_value 'ui_show_detail', im_urs.meta_value 'ui_row_start', im_ure.meta_value 'ui_row_end', im_ucs.meta_value 'ui_col_start', im_uce.meta_value 'ui_col_end', im_tao.meta_value 'track_as_observation', im_uip.meta_value 'ui_placeholder'
	from rules_pcp p
        Left join     rules_items i ON i.pcp_id = p.pcp_id and p.status = 'active'
        Left join     rules_items si ON si.items_id = i.items_parent and p.status = 'active'
        Left Join     rules_ucp u ON u.items_id = i.items_id and u.user_id = " . $user_id . " and u.meta_key != 'TOD'
        Left Join     rules_ucp its ON its.items_id = i.items_id and its.meta_key = 'status'  AND its.user_id = " . $user_id . "
 		Left Join 	rules_itemmeta imf on imf.items_id = i.items_id and imf.meta_key = 'ui_fld_type'
 		Left Join 	rules_itemmeta im_ro on im_ro.items_id = i.items_id and im_ro.meta_key = 'radio_options'
 		Left Join 	rules_itemmeta im_usd on im_usd.items_id = i.items_id and im_usd.meta_key = 'ui_show_detail'
 		Left Join 	rules_itemmeta im_urs on im_urs.items_id = i.items_id and im_urs.meta_key = 'ui_row_start'
 		Left Join 	rules_itemmeta im_ure on im_ure.items_id = i.items_id and im_ure.meta_key = 'ui_row_end'
 		Left Join 	rules_itemmeta im_ucs on im_ucs.items_id = i.items_id and im_ucs.meta_key = 'ui_col_start'
 		Left Join 	rules_itemmeta im_uce on im_uce.items_id = i.items_id and im_uce.meta_key = 'ui_col_end'
 		Left Join 	rules_itemmeta im_tao on im_tao.items_id = i.items_id and im_tao.meta_key = 'track_as_observation'
 		Left Join 	rules_itemmeta im_uip on im_uip.items_id = i.items_id and im_uip.meta_key = 'ui_placeholder'
 		Left Join 	rules_itemmeta ims on ims.items_id = i.items_id and ims.meta_key = 'ui_sort'
 		Left Join 	rules_itemmeta imv on imv.items_id = i.items_id and imv.meta_key = 'ui_fld_select'
 		left join 	rules_itemmeta imd ON imd.items_id = i.items_id AND imd.meta_key = 'ui_default'
	where cpset_id = 1
		and p.prov_id = " . $id . "
		and p.section_text = '" . $section_text . "'
		and imf.meta_value <> ''
order by ui_sort
 -- and p.status = 'active'
;";

        $results = DB::connection('mysql_no_prefix')->select(DB::raw($sql_ucp));

        if (isset($results)) {
            //dd($results);
        } else {
            die('no result');

            return false;
        }

        $items = [];
        $sub_meta = [];
        foreach ($results as $row) {
            //echo "<pre>";var_dump($row);echo "</pre>";
            $sections[$row->section_text] = $row->status;

            if ($row->items_parent > 0) {
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['value'] = $row->meta_value;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['items_id'] = $row->items_id;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_fld_type'] = $row->ui_fld_type;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['radio_options'] = $row->radio_options;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_sort'] = $row->ui_sort;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['items_parent'] = $row->items_parent;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ucp_id'] = $row->ucp_id;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['pcp_id'] = $row->pcp_id;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_show_detail'] = $row->ui_show_detail;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_row_start'] = $row->ui_row_start;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_row_end'] = $row->ui_row_end;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_col_start'] = $row->ui_col_start;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_col_end'] = $row->ui_col_end;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_default'] = $row->ui_default;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['track_as_observation'] = $row->track_as_observation;
                $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_placeholder'] = $row->ui_placeholder;
                if ($row->ui_fld_type == 'SELECT') {
                    $sub_meta[$row->section_text][$row->sub_parent][$row->items_text]['ui_fld_select'] = $row->ui_fld_select;
                }
            } else {
                $items[$row->section_text][$row->items_text] = null;
                $sub_meta[$row->section_text][$row->items_parent][$row->items_text]['items_id'] = $row->items_id;
                $sub_meta[$row->section_text][$row->items_parent][$row->items_text]['item_status'] = $row->item_status;
                $sub_meta[$row->section_text][$row->items_parent][$row->items_text]['ui_sort'] = $row->ui_sort;
                $sub_meta[$row->section_text][$row->items_parent][$row->items_text]['pcp_id'] = $row->pcp_id;
            }

            if (!isset($items[$row->section_text][$row->sub_parent])) {
                if (!isset($items[$row->section_text][$row->items_text])) {
                    if ($row->meta_key == 'status') {
                        $items[$row->section_text][$row->items_text] = [
                            'status'  => $row->item_status,
                            'ucp_id'  => $row->ucp_id,
                            'ui_sort' => $row->ui_sort,
                        ];
                    }
                }
            }
        }

        return [
            'items'    => $items,
            'sub_meta' => $sub_meta,
        ];
    }
}
