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
$field.="\"<input type='hidden' id='hidden_selected_element_input' name='participating[]' value='\" + ui.item.value + \"'>";
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
            
            // . '     $(this).val(ui.item.value); '
            // . '     $(".magic_search_box_wrapper).append(ui.item.value);''
            .   '$(".fas.fa-times").click(function(){$(this).parent().remove();}); '
            .   '} ',
            'close' => 'function( event, ui ) {$("#user_search_box").val("");}',

        ],
        //html options
        'options' => $this->context->html_params,
    ]);
    echo "<div class='hidden_element_box'>";
    foreach ($this->context->participating as $part)
    {
        // print_r($currentUser);
        // print_r(" " . $part);
        echo "<div class='hidden_selected_element'>";
        if ($currentUser!=$part)
        {
            echo "<i class='fas fa-times'></i>";
        }
        else
        {
            
        }
        echo "$part<input type='hidden' id='hidden_selected_element_input' name='participating[]' value='$part'>";
        echo "</div>";
    }
    echo "</div>";
    /*
     * Print hidden inputs with same name for all selected elements, 
     * in order to resend them as parameters on new submits!
     */
    // $tag = Html::tag('i', "", ['class' => 'fa fa-times',]);  
    // /*
    //  * First three results should be displayed
    //  */
    // foreach(array_slice($this->context->selected_elements, 0, 3) as $element)
    // {
    //     echo Html::beginTag('div', ['class' => 'hidden_element_box']);
    //     echo Html::tag('div', $element . $tag , ['class' => 'hidden_selected_element']);
    //     echo Html::hiddenInput($this->context->html_params["name"] . "[]", $element); 
    //     echo Html::endTag('div');
    // }
    // /*
    //  * Remaining results should be hidden
    //  */
    // foreach(array_slice($this->context->selected_elements, 3) as $element)
    // {
    //     echo Html::beginTag('div', ['class' => 'hidden_element_box non-display']);
    //     echo Html::tag('div', $element . $tag , ['class' => 'hidden_selected_element']);
    //     echo Html::hiddenInput($this->context->html_params["name"] . "[]", $element); 
    //     echo Html::endTag('div');        
    // }
    // if(count($this->context->selected_elements) > 3)
    // {
    //     echo Html::a("Show all", null, ['class'=> 'magic_search_box_reveal']);
    // }

    // //show "clear all" button when more than one element exists
    // if(count($this->context->selected_elements) > 1){
    //     echo Html::a("Clear all", null, ['class'=> 'magic_search_box_clear_all']);
    // }
    
echo "</div>";


?>
