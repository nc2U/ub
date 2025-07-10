<?php
echo '<div class="col-xs-12 mb20" style="padding: 0;">
<div id="myCarousel" class="carousel slide" data-ride="carousel">
  <!-- Indicators -->
  <ol class="carousel-indicators">
    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
    <li data-target="#myCarousel" data-slide-to="1"></li>
    <li data-target="#myCarousel" data-slide-to="2"></li>
  </ol>

  <!-- Wrapper for slides -->
  <div class="carousel-inner">
    <div class="item active">
      <img src="uploads/img/main1.jpg" alt="Slide 1" style="">
      <div class="carousel-caption">
        <!-- <p>Description for Slide 1.</p> -->
      </div>
    </div>

    <div class="item">
      <img src="uploads/img/main2.jpg" alt="Slide 2">
      <div class="carousel-caption">
       <!-- <p>Description for Slide 2.</p> -->
      </div>
    </div>

    <div class="item">
      <img src="uploads/img/main3.jpg" alt="Slide 3">
      <div class="carousel-caption">
        <!-- <p>Description for Slide 3.</p> -->
      </div>
    </div>
  </div>

  <!-- Left and right controls -->
  <a class="left carousel-control" href="#myCarousel" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#myCarousel" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right"></span>
    <span class="sr-only">Next</span>
  </a>
</div>
</div>';

$active = element('bl', $view) === '2';

if ($active) {
    echo ' <ul class="nav nav-tabs" style="margin-bottom: 10px;">
  <li class="nav-item">
    <a class="nav-link" href="?bl=2">조합원 커뮤니티</a>
  </li>
  <li class="nav-item active">
    <a class="nav-link" href="?bl=1">조합 공개 자료</a>
  </li>
</ul>';
} else {
    echo ' <ul class="nav nav-tabs" style="margin-bottom: 10px;">
  <li class="nav-item active">
    <a class="nav-link" href="?bl=2">조합원 커뮤니티</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="?bl=1">조합 공개 자료</a>
  </li>
</ul>';
}

$k = 0;
$is_open = false;
if (element('board_list', $view)) {
	foreach (element('board_list', $view) as $key => $board) {
		$config = array(
			'skin' => 'bootstrap',
			'brd_key' => element('brd_key', $board),
			'limit' => 5,
			'length' => 40,
			'is_gallery' => '',
			'image_width' => '',
			'image_height' => '',
			'cache_minute' => 1,
		);
		if ($k % 2 === 0) {
			echo '<div class="row">';
			$is_open = true;
		}
		echo $this->board->latest($config);
		if ($k % 2 === 1) {
			echo '</div>';
			$is_open = false;
		}
		$k++;
	}
}

if ($is_open) {
	echo '
	<div class="col-md-6">
	<div class="panel panel-default">

	<div class="panel-heading">조합원 문의</div>

	<div class="table-responsive">
	<div style="padding: 8px;">
	<div style="padding: 7px;"><span class="info-span">업무대행사 : </span>'.$this->cbconfig->item('company_biz_agency').' ('.$this->cbconfig->item('company_agency_phone').')</div>
	<div style="padding: 7px;"><span class="info-span">사무실 전화번호 : </span>'.$this->cbconfig->item('company_phone').'</div>
	<div style="padding: 7px;"><span class="info-span">사무실 팩스번호 : </span>'.$this->cbconfig->item('company_fax').'</div>
	<div style="padding: 7px;"><span class="info-span">주 소 : </span>'.$this->cbconfig->item('company_address').'</div>
	<div style="padding: 7px;"><span class="info-span">문 의 시 간 : </span>평일 10:00 ~ 18:00 (<span style="color: red">주말, 공휴일 휴무</span>)</div>
	</div>
	</div>
	
	</div>
	</div>
	';
	echo '</div>';
	$is_open = false;
}
