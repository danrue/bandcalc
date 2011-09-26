<?php
/* 
 * Copyright 2005, 2011 Dan Rue <drue@therub.org>. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *    2. Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY Dan Rue ''AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
 * EVENT SHALL Dan Rue OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of Dan Rue.
 */ 

/*
 * Stand alone utility to convert between various units of
 * bandwidth and time.
 */

// bit conversion constants
define ("TB", 1099511627776);
define ("GB", 1073741824);
define ("MB", 1048576);
define ("KB", 1024);

$transferbuf=""; //answer buffer
$tb = $gb = $mb = $kb = 0;
$gbps = $mbps = $kbps = 0;
$days = $hours = $minutes = $seconds = 0;
$pretty_filesize = $pretty_bandwidth = $pretty_time = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    /* What are we calculating? */
    if (isset($_POST['calcfilesize'])){
        $calctype = "calcfilesize";
    }
    else if (isset($_POST['calcbandwidth'])){
        $calctype = "calcbandwidth";
    }
    else if (isset($_POST['calctime'])){
        $calctype = "calctime";
    }
    else {
        die("You didn't choose a proper calculation type");
    }


    /* Process file sizes */
    if ($calctype != "calcfilesize"){
        if ($_POST['tb'] != "")
            $tb = $_POST['tb'];
        else
            $tb = 0;
        if ($_POST['gb'] != "")
            $gb = $_POST['gb'];
        else
            $gb = 0;
        if ($_POST['mb'] != "")
            $mb = $_POST['mb'];
        else
            $mb = 0;
        if ($_POST['kb'] != "")
            $kb = $_POST['kb'];
        else
            $kb = 0;

        if (!is_numeric($tb) || !is_numeric($gb) || \
            !is_numeric($mb) || !is_numeric($kb)){
            die("Please enter numeric values only");
        }

        $totalbytes = ($tb*TB)+($gb*GB)+($mb*MB)+($mb*KB);
    }


    /* Process bandwidth values */
    if ($calctype != "calcbandwidth"){
        if ($_POST['gbps'] != "")
            $gbps = $_POST['gbps'];
        else
            $gbps = 0;
        if ($_POST['mbps'] != "")
            $mbps = $_POST['mbps'];
        else
            $mbps = 0;
        if ($_POST['kbps'] != "")
            $kbps = $_POST['kbps'];
        else
            $kbps = 0;

        if (!is_numeric($gbps) || !is_numeric($mbps) || !is_numeric($kbps)){
            die("Please enter numeric values only");
        }

        $totalbytespersecond = (($gbps*GB)/8) + (($mbps*MB)/8) + (($kbps*KB)/8);
    }


    /* Process time fields */
    if ($calctype != "calctime"){
        if ($_POST['days'] != "")
            $days = $_POST['days'];
        else
            $days = 0;
        if ($_POST['hours'] != "")
            $hours = $_POST['hours'];
        else
            $hours = 0;
        if ($_POST['minutes'] != "")
            $minutes = $_POST['minutes'];
        else
            $minutes = 0;
        if ($_POST['seconds'] != "")
            $seconds = $_POST['seconds'];
        else
            $seconds = 0;

        if (!is_numeric($days) || !is_numeric($hours) || \
            !is_numeric($minutes) || !is_numeric($seconds)){
            die("Please enter numeric values only");
        }
        $totalseconds = ($days*86400)+($hours*3600)+($minutes*60)+$seconds;
    }    


    /* Do the calculations */
    switch ($calctype){
        case "calcfilesize":
            $totalbytes = bcmul($totalseconds,$totalbytespersecond);
            $gb = bcdiv($totalbytes,TB);
            $mb = bcdiv(bcmod($totalbytes,TB),GB);
            $mb = bcdiv(bcmod($totalbytes,GB),MB);
            $kb = bcdiv(bcmod($totalbytes,MB),KB);
            if ($tb > 0){
                $remainder = substr(bcmod($totalbytes, TB), 0, 3);
                $pretty_filesize = sprintf("(<b>%s.%s TB</b>)", $tb, $remainder);
            } else if ($gb > 0){
                $remainder = substr(bcmod($totalbytes, GB), 0, 3);
                $pretty_filesize = sprintf("(<b>%s.%s GB</b>)", $gb, $remainder);
            } else if ($mb > 0){
                $remainder = substr(bcmod($totalbytes, MB), 0, 3);
                $pretty_filesize = sprintf("(<b>%s.%s MB</b>)", $mb, $remainder);
            } else if ($kb > 0){
                $remainder = substr(bcmod($totalbytes, KB), 0, 3);
                $pretty_filesize = sprintf("(<b>%s.%s KB</b>)", $kb, $remainder);

            }
            break;

        case "calcbandwidth":
            //$totalbytespersecond = $totalbytes/$totalseconds;
            //$mbps = intval(($totalbytespersecond*8)/MB);
            //$kbps = intval((($totalbytespersecond*8)%MB)/KB);
            $totalbytespersecond = bcdiv($totalbytes,$totalseconds);
            $gbps = bcdiv(bcmul($totalbytespersecond,"8"),GB);
            $mbps = bcdiv(bcmod(bcmul($totalbytespersecond,"8"),GB),MB);
            $kbps = bcdiv(bcmod(bcmul($totalbytespersecond,"8"),MB),KB);
            if ($gbps > 0){
                $remainder = substr(bcmod($totalbytespersecond, GB), 0, 3);
                $pretty_bandwidth = sprintf("(<b>%s.%s gbps</b>)", 
                                            $gbps, $remainder);
            } else if ($mbps > 0){
                $remainder = substr(bcmod($totalbytespersecond, MB), 0, 3);
                $pretty_bandwidth = sprintf("(<b>%s.%s mbps</b>)", 
                                            $mbps, $remainder);
            } else if ($kb > 0){
                $remainder = substr(bcmod($totalbytespersecond, KB), 0, 3);
                $pretty_bandwidth = sprintf("(<b>%s.%s kbps</b>)", 
                                            $kbps, $remainder);
            }

            break;

        case "calctime":
            $totalseconds = intval($totalbytes/$totalbytespersecond);
            $days = intval($totalseconds/86400);
            $hours = intval($totalseconds/3600);
            $minutes = intval(($totalseconds/60)%60);
            $seconds = $totalseconds%60;
            if ($days > 0){
                $pretty_time = sprintf("<b>%dd: %dh:%dm:%ds</b>", 
                                       $days, $hours, $minutes, $seconds);
            } else if ($hours > 0) {
                $pretty_time = sprintf("<b>%dh:%dm:%ds</b>", 
                                       $hours, $minutes, $seconds);
            } else if ($minutes > 0) {
                $pretty_time = sprintf("<b>%dm:%ds</b>", $minutes, $seconds);
            } else if ($seconds > 0) {
                $pretty_time = sprintf("<b>%ds</b>", $seconds);
            }
            break;
    }

}

?>

<table>
<tr><td>
<h2>Simple Bandwidth Calculator</h2>
<h4>Fill in any two rows and then calculate the missing row</h4>
</td></tr>
<tr><td>

    <table>
    <form action="" method="post">
    <tr><td>
    <input type="submit" name="calcfilesize" value="Calc-->"></td><td>
    Filesize: </td><td>
        <input type="text" name="tb" size="4" value="<?php echo $gb?>">TB +
        <input type="text" name="gb" size="4" value="<?php echo $gb?>">GB +
        <input type="text" name="mb" size="4" value="<?php echo $mb?>">MB +
        <input type="text" name="kb" size="4" value="<?php echo $kb?>">KB
        <?php echo $pretty_filesize;?>
    </td></tr>
    <tr><td>
    <input type="submit" name="calcbandwidth" value="Calc-->"></td><td>
    Bandwidth: </td><td>
        <input type="text" name="gbps" size="4" value="<?php echo $gbps?>">gbps +
        <input type="text" name="mbps" size="4" value="<?php echo $mbps?>">mbps +
        <input type="text" name="kbps" size="4" value="<?php echo $kbps?>">kbps
        <?php echo $pretty_bandwidth;?>
    </td></tr>
    <tr><td>
    <input type="submit" name="calctime" value="Calc-->"></td><td>
    Transfer Time: </td><td>
        <input type="text" name="days" size="2" value="<?php echo $days?>">d +
        <input type="text" name="hours" size="2" value="<?php echo $hours?>">h +
        <input type="text" name="minutes" size="2" value="<?php echo $minutes?>">m +
        <input type="text" name="seconds" size="2" value="<?php echo $seconds?>">s
        <?php echo $pretty_time;?>
    </td></tr>

    </form>
    </table>
<br />

<small>* calc uses 8 bits/byte, and does not correct for real world factors.
In other words, this will calculate the theoretical best case.  Real world
transfers are generally at least 10% slower.</small>

</td></tr>
</table>

