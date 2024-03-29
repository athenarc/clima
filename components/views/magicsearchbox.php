<?php

/* 
 * Magic search box view!
 * 
 * @author: Ilias Kanellos (First Version: September 2015)
 * @author: Serafeim Catzopoulos (Last Modified: May 2016)
 * 
 */
namespace yii\jui;

use yii\jui\AutoComplete;
use yii\helpers\Html;



/*
 * Include widget css
 */
echo Html::cssFile('@web/css/components/magic_search_box.css');
//echo Html::jsFile('@web/js/components/widgets/magic_search_box.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
/*
 * Javascript files should be registered at the end of a page, in order to be more efficient in page loading etc.
 * Therefore the helper used for registering JS files is not suitable - at least until we find out how to make it
 * append scripts at the end of the html code. Instead, we use the classic register method here.
 */
$this->registerJsFile('@web/js/components/magic_search_box.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

/*
 * Echo all view code
 */

$field="\"<div class='hidden_selected_element'><i class='fas fa-times'></i>&nbsp;\" + ui.item.value + ";
$field.="\"<input type='hidden' class='hidden_selected_element_input' name='participating[]' value='\" + ui.item.value + \"'>";
$field.="</div>\"";

echo "<div class='magic_search_box_wrapper'>";
    echo AutoComplete::widget([
        'name' => 'user_search_box',
        'clientOptions' =>
        [
            'minLength' => $this->context->min_char_to_start,
            'source' => $this->context->ajax_action . 
                               "&expansion=" . $this->context->expansion . 
                               "&max_num=" . $this->context->suggestions_num,
        ],
        'clientEvents' =>
        [
            'select' => 
                'function(event, ui)'
            .   '{ '
                    /*
                     * Get the value selected, add it to the list of names and 
                     * also add the handle for the remove button.
                     */
            . '     if(ui.item.value == "No suggestions found") return false; '
            . '     var selected_elements=$(".hidden_element_box").html(); '
            . '     selected_elements=' . $field . ' + selected_elements; '
            . '     $(".hidden_element_box").html(selected_elements); '
            . '     var elements=$(".hidden_selected_element").length; '
            . '     var el_html="Total: " + elements;'
            . '     $("#total-users").html(el_html);'
            
            .   '$(".fas.fa-times").click(function(){$(this).parent().remove();var elements=$(".hidden_selected_element").length;var el_html="Total: " + elements;$("#total-users").html(el_html);}); '
            .   '} ',
            'close' => 'function( event, ui ) {$("#user_search_box").val("");}',

        ],
        //html options
        'options' => $this->context->html_params,
    ]);
    echo "<div class='hidden_element_box col-md-10'>";
    foreach ($this->context->participating as $part)
    {
        // print_r($currentUser);
        // print_r(" " . $part);
        echo "<div class='hidden_selected_element'>";
        if ($currentUser!=$part)
        {
            echo "<i class='fas fa-times'></i>";
        }
        
        echo " $part<input type='hidden' class='hidden_selected_element_input' name='participating[]' value='$part'>";
        echo "</div>";
    }
    echo "</div>";
    echo "<div class='col-md-2 text-right' id='total-users'>Total: " . count($this->context->participating) . "</div>";
    
echo "</div>";


?>
