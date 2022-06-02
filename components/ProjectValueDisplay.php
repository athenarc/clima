<?php

namespace app\components;

/**
* A class created for organizing and producing formats of values of common attributes shared amongst the projects
 */
class ProjectValueDisplay
{

    /**
    * @return string
    */
    public static function endDate($ends, $remainingTime, $requestHistory=null, $targetField='end_date') {
        $renderedHtml='';
        if (isset($requestHistory['diff']['project'][$targetField])) $renderedHtml.=ProjectDiff::str($requestHistory['diff']['project'][$targetField]['other'], $requestHistory['diff']['project'][$targetField]['current']);
        else $renderedHtml.=$ends;
        $renderedHtml.= ' ('.$remainingTime.' days remaining';
        if (isset($requestHistory['diff']['project'][$targetField])) {
            $renderedHtml.=' - <span class="text-'
                . (($requestHistory['diff']['project'][$targetField]['difference'] > 0) ? 'danger' : 'success')
                . '">' . abs($requestHistory['diff']['project'][$targetField]['difference'])
                . ' days '
                . (($requestHistory['diff']['project'][$targetField]['difference'] > 0) ? ' to be extended' : 'to be shortened')
                . '</span>';
        }
        $renderedHtml.=')';

        return $renderedHtml;
    }

    public static function userList($userList, $numberOfUsers, $maximumNumberOfUsers, $requestHistory = null,$targetFieldUserList='user_list',$targetFieldUserNum='user_num') {
        $renderedHtml='';
        if (isset($requestHistory['diff']['project'][$targetFieldUserList])) $renderedHtml .= ProjectDiff::arr($requestHistory['diff']['project'][$targetFieldUserList]['other'], $requestHistory['diff']['project'][$targetFieldUserList]['current']);
        else $renderedHtml .= $userList;
        $renderedHtml.=' ('.$numberOfUsers.' out of ';
        if (isset($requestHistory['diff']['project'][$targetFieldUserNum])) $renderedHtml.= ProjectDiff::str($requestHistory['diff']['project'][$targetFieldUserNum]['other'], $requestHistory['diff']['project'][$targetFieldUserNum]['current']);
        else $renderedHtml .= $maximumNumberOfUsers;
        $renderedHtml .=')';

        return $renderedHtml;
    }

    public static function startDate($start, $requestHistory=null, $targetFieldSubmissionDate='submission_date', $targetFieldApprovalDate='approval_date'){
        $renderedHtml = '';
        if (isset($requestHistory['diff']['project'][$targetFieldSubmissionDate]) && isset($requestHistory['diff']['project'][$targetFieldApprovalDate])) $renderedHtml .= ProjectDiff::str($requestHistory['diff']['project'][$targetFieldApprovalDate]['other'], $requestHistory['diff']['project'][$targetFieldSubmissionDate]['current']);
        else $renderedHtml .= $start;

        return $renderedHtml;
    }
//
    public static function resource($resourceValue, $targetFieldResource, $requestHistory = null, $resourcesStats = null, $context = null)
    {
        $contextManager = ResourceContext::getContextManager($context);
        $renderedHtml = '';
        $renderedHtml .= '
            <div class="row mr-0">
                <div class="col-12 col-lg-6 text-left">
        ';
        if (isset($requestHistory['diff']['details'][$targetFieldResource]['current']) && isset($requestHistory['diff']['details'][$targetFieldResource]['other'])) {
            $renderedHtml .= ProjectDiff::str(explode(' ', $contextManager($requestHistory['diff']['details'][$targetFieldResource]['other']))[0], $contextManager($requestHistory['diff']['details'][$targetFieldResource]['current']));
        } else $renderedHtml .= $contextManager($resourceValue);

        if (isset($requestHistory['diff']['details'][$targetFieldResource]['difference'])) {
            $renderedHtml .= ' (<span class="text-'
                . (($requestHistory['diff']['details'][$targetFieldResource]['difference'] > 0) ? 'danger' : 'success')
                . '">'
                . $contextManager(abs($requestHistory['diff']['details'][$targetFieldResource]['difference']))
                . ' in total to be '
                . (($requestHistory['diff']['details'][$targetFieldResource]['difference'] > 0) ? 'allocated' : 'released')
                . '</span>)';
        }
        $renderedHtml .= '
                </div>
                <div class="col-12 col-lg-6 text-right pr-0">
        ';
        if (isset($resourcesStats[$targetFieldResource])) {
            $loadIndicatorArgs = [
                'current' => $resourcesStats[$targetFieldResource]['current'],
                'requested' => $resourcesStats[$targetFieldResource]['requested'],
                'total' => $resourcesStats[$targetFieldResource]['total'],
                'contextManager' => $contextManager,
                'currentMessageSuffix' => ' reserved',
                'requestedMessageSuffix' => ' requested',
                'remainingMessageSuffix' => ' remaining',
                'loadBreakpoint0' => 0.33,
                'loadBreakpoint1' => 0.66,
                'bootstrap4RequestedClassPositive' => 'primary',
                'bootstrap4RequestedClassNegative' => 'secondary'
            ];

            $renderedHtml .= ColorClassedLoadIndicator::widget($loadIndicatorArgs);
        }
        $renderedHtml .= '
                </div>
            </div>
        ';

        return $renderedHtml;
    }

    public static function simpleValue($defaultValue, $targetField, $requestHistory=null) {
        $renderedHtml = '';
        if (isset($requestHistory['diff']['details'][$targetField]['current']) && isset($requestHistory['diff']['details'][$targetField]['other'])) {
            $renderedHtml.= ProjectDiff::str($requestHistory['diff']['details'][$targetField]['other'], $requestHistory['diff']['details'][$targetField]['current']);
        }
        else $renderedHtml .= $defaultValue;

        return $renderedHtml;
    }
}
