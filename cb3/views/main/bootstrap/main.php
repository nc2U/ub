<?php
echo '<img src="http://dongchun1-9.com/img/main/visual_m.jpg" width="1170px" style="margin-bottom: 20px" />';

echo '<ul class="nav nav-tabs" style="margin-bottom: 10px;">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="#">조합 공개 자료</a>
  </li>
  <li class="nav-item active">
    <a class="nav-link" href="#">조합원 커뮤니티</a>
  </li>
</ul>';

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
	<div class="panel panel-success">

	<div class="panel-heading">조합원 문의</div>

	<div class="table-responsive">
	<div style="padding: 8px;">
	<div style="padding: 7px;">사무실 전화번호 : 032-833-3654</div>
	<div style="padding: 7px;">(주) 바램디앤씨 : 032-833-3654 (업무대행사)</div>
	<div style="padding: 7px;">팩 스 번 호 : 032-833-3654</div>
	<div style="padding: 7px;">문 의 시 간 : 평일 10:00 ~ 18:00 (<span style="color: red">토, 일, 공휴일 휴무</span>)</div>
	<div style="padding: 7px;">주 소 : 인천광역시 연수구 능허대로289번길 21 대성빌딩 4층</div>
	</div>
	</div>
	
	</div>
	</div>
	
	';
	echo '</div>';
	$is_open = false;
}
