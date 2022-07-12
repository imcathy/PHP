<?php
    /*
        @desc :
            - 페이징 태그 생성
            - fnGetHref() 함수를 이용하여 페이징 관련 파라미터를 제외한 주소값을 가져와서 링크 생성
            - fnPaginator()는 fnGetHref()와 함께 존재해야 함.
        @param :
            - $totalRecords : 총 레코드 수
            - $recordsPerPage : 한 페이지에 보일 레코드 수
            - $pagePerBlock : 한번에 보일 페이지 블럭 수 ex) 10 ->  1 2 3 4 5 6 7 8 9 10
            - $currentPage : 현재 페이지
        @return : 페이징 태그 생성
    */
    function fnPaginator($totalRecords, $recordsPerPage, $pagePerBlock, $currentPage){
        $href = fnGetHref(); // 기존 URI + currentPage 를 제외한 파라미터를 문자열로 생성
        $requestURI = $href.((strpos($href,"?")  <= 0) ? "?" : "&"); // $href에 ?가 있으면 끝에 &를 붙이고 없으면 ?를 붙인다.

        // 페이징 계산   1000 / 10
        $totalPages = ceil($totalRecords/$recordsPerPage); // 총 페이지 수
        if(!$currentPage)
            $currentPage = 1;
        $pageIndex = ceil($currentPage/$pagePerBlock)-1;  // 몇 번째 페이지인지 ex) 1페이지 → $pageIndex = 0

        // html 태그 생성
        $pagingHtml = "";
        $pagingHtml .= "<div>";
        $pagingHtml .= "<ul class='pagination'>";

        if($pageIndex>0) { // 첫번째 페이지($pageIndex = 0)가 아닌 경우 '처음으로', '이전' 버튼 활성화
            $pagingHtml .= "<li><a href='$requestURI" . "currentPage=1'><img src=\"btn_page_first.png\" alt=\"처음\"/></a></li>";
            $prevPage = ($pageIndex)*$pagePerBlock;
            $pagingHtml .= "<li><a href='$requestURI" . "currentPage=$prevPage'><img src=\"btn_page_prev.png\" alt=\"이전\"/></a></li>";
        }else{ // 첫번째 페이지($pageIndex = 0)일 경우 '처음으로', '이전' 버튼 비활성화
            $pagingHtml .= "<li class='disabled'><a href='#'><img src=\"btn_page_first.png\" alt=\"처음\"/></a></li>";
            $pagingHtml .= "<li class='disabled'><a href='#'><img src=\"btn_page_prev.png\" alt=\"이전\"/></a></li>";
        }

        $pageEnd=($pageIndex+1)*$pagePerBlock; // 현재 페이지 블럭의 마지막 페이지
        if($pageEnd>$totalPages)
            $pageEnd=$totalPages;

        for($setPage=$pageIndex*$pagePerBlock+1;$setPage<=$pageEnd;$setPage++){ // 페이지 생성
            if($setPage==$currentPage){
                $pagingHtml .= "<li class='current'><a class='current' href='#'>$setPage</a></li>";// 기존 class값 active
            }else{
                $pagingHtml .= "<li><a href='$requestURI" . "currentPage=$setPage'>$setPage</a></li>";
            }
        }

        if($pageEnd<$totalPages){ // 마지막 페이지 블럭이 아닌 경우 '다음', '끝으로' 버튼 활성화
            $nextPage = ($pageIndex+1)*$pagePerBlock+1;
            $pagingHtml .= "<li><a href='$requestURI" . "currentPage=$nextPage'><img src=\"btn_page_next.png\" alt=\"다음\"/></a></li>";
            $pagingHtml .= "<li><a href='$requestURI" . "currentPage=$totalPages'><img src=\"btn_page_end.png\" alt=\"끝\"/></a></li>";

        }else{ // 마지막 페이지 블럭인 경우 '다음', '끝으로' 버튼 비활성화
            $pagingHtml .= "<li class='disabled'><a href='#'><img src=\"btn_page_next.png\" alt=\"다음\"/></a></li>";
            $pagingHtml .= "<li class='disabled'><a href='#'><img src=\"btn_page_end.png\" alt=\"끝\"/></a></li>";
        }
        $pagingHtml .= "</ul>";
        $pagingHtml .= "</div>";

        return $pagingHtml; // 완성된 태그 return
    }

    function fnGetHref(){
        $href = $_SERVER['SCRIPT_NAME']; // 현재 페이지 주소

        $i=0;
        foreach($_REQUEST as $key => $data){ // request로 넘어온 값들 체크
            if($data != ''){
                if($key != "currentPage"){ // 페이지 관련 파라미터가 아닌 파라미터로만 문자열 생성
                    if($i==0){
                        $href.="?"; // 첫 파라미터 앞에는 ?
                    }else{
                        $href.="&"; // 첫 파라미터가 아닌 경우에는 &
                    }
                    /* 배열 전달 부분 */
                    if(is_array($data)){
                        //$arrData = implode("^", $data);
                        for($i=0;$i<sizeof($data);$i++){
                            $href.=$key."[]=".fnNoInjection($data[$i]);
                            if($i<sizeof($data)-1){
                                $href.= '&';
                            }
                        }
                    }else{
                        $href.=$key."=".urlencode($data); // param=value 형태로 주소 값 생성
                    }
                    $i++; // 아래에서 이동
                }
            }
        }
        return $href;
    }
  ?>
