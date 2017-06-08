<?php

/**
 * Calculate pagination
 * @param integer page Current page
 * @param integer rpp Result per page
 * @param integer total Total result
 * @param integer pts Total page link to show
 * @param array cond Additional URL get parameters
 * @param string prev The prev label
 * @param string next The next label
 * @return array
 */
function calculate_pagination($page, $rpp, $total, $pts=10, $conds=[], $prev_label='&#171;', $next_label='&#187;'){
    $result = [];
    
    if($rpp >= $total)
        return [];
    
    if(!$page || $page < 1)
        $page = 1;
    
    $total_page = ceil($total/$rpp);
    
    $first_page = $page - floor( $pts / 2 );
    if($first_page < 1)
        $first_page = 1;
    
    $last_page = $first_page + ($pts-1);
    if($last_page > $total_page)
        $last_page = $total_page;
    
    $first_page = $last_page - ($pts-1);
    if($first_page < 1)
        $first_page = 1;
    
    $prev = $page - 1;
    if($prev < 1)
        $prev = '#';
    if($prev == '#')
        $result[$prev_label] = ($prev=='#'?'#':'?page='.$prev);
    else{
        $cond_qry = $conds;
        $cond_qry['page'] = $prev;
        $result[$prev_label] = '?' . http_build_query($cond_qry);
    }
    
    for($i=$first_page;$i<=$last_page;$i++){
        if($i == $page)
            $result[$i] = '#';
        else{
            $cond_qry = $conds;
            if($i != 1)
                $cond_qry['page'] = $i;
            $result[$i] = '?' . http_build_query($cond_qry);
        }
    }
    
    $next = $page + 1;
    if($next > $total_page)
        $next = '#';
    if($next == '#')
        $result[$next_label] = '#';
    else{
        $cond_qry = $conds;
        $cond_qry['page'] = $next;
        $result[$next_label] = '?' . http_build_query($cond_qry);
    }
    
    return $result;
}