<?php
    /*
        @desc : 파일 업로드 체크 (보안검사, 확장자, 파일크기, MimeType 검사)
        @param :
            - $objFile : 파일 오브젝트 $_FILES[INPUT NAME]
            - $strFileKind : 파일 검증 종류 - img, excel, 그 외(허용가능 파일)
            - $intFileSizeLimit : 파일 크기 (kb 단위)

        @return :
             - 배열 : resultCode, resultMsg
             - resultCode
                1 : 성공
                0 : 실패

                -1 : 실패(보안위배)
                -2 : 실패(용량초과)
                -3 : 실패(파일확장자)
                -4 : 실패(파일MIME)
    */
    function fnFileUploadCheck($objFile, $strFileKind, $intFileSizeLimit){

        $strFileName = $objFile["name"];                                        // 파일 명
        $strFileExt = strtolower(pathinfo($strFileName, PATHINFO_EXTENSION));    // 파일 확장자
        $strFileMime = strtolower(mime_content_type($objFile["tmp_name"]));        // 파일 MIME 타입
        $intFileSize = $objFile["size"] / 1024 ;                                // 파일 크기 (KB)

        $arrayNotAllowExt = array("php", "php3", "php4", "htm", "html", "asp", "aspx", "cer", "cdx", "asa", "jsp", "war"); // 서버 공격 위험 확장자
        $arrayNotAllowStr = array(";", "%00", "%zz"); // 서버 우회 공격 위험 문자 (가상의 확장자를 파일명에 넣고  apache허점을 이용한 서버 언어 실행 공격)

        $arrayImgExt = array("jpg", "gif", "jpeg", "bmp", "png", "zip", "pdf"); // 이미지 허용 확장자
        $arrayExcelExt = array("xls", "xlsx"); // 엑셀 허용 확장자
        $arrayAllowExt = array("jpg", "gif", "jpg", "bmp", "png", "xls", "xlsx", "pdf", "doc", "docx", "zip", "ppt", "pptx", "mp3", "csv", "swf"); // 그 외 허용 확장자

        $arrayImgMime = array("image/jpg", "image/pjpeg", "image/jpeg", "image/gif", "image/bmp", "image/png", "application/zip", "multipart/x-zip","application/x-zip-compressed", "application/pdf"); // 이미지 허용 MimeType
        $arrayExcelMime = array("application/vnd.ms-excel"); // 엑셀 허용 MimeType
        $arrayAllowMime = array("image/jpg", "image/pjpeg", "image/jpeg", "image/gif", "image/bmp", "image/png", "application/vnd.ms-excel", "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/x-zip-compressed", "application/vnd.ms-powerpoint", "audio/mpeg", "application/x-shockwave-flash", "text/plain", "application/octet-stream", "video/x-ms-wmv"); // 그 외 허용 MimeType

        $resultCode = 1; // default: 성공

        /* ------------- 서버 공격 위험 확장자 필터링 --------------- */
        if(!empty($strFileExt) && in_array($strFileExt, $arrayNotAllowExt) > 0){
            $resultCode = -1;
            return $resultCode;
        }
        /*------------------------------------------------------------*/

        /*-------- 서버 우회 공격이 가능한 단어일 경우 필터링 --------*/
        if(in_array($strFileName, $arrayNotAllowStr) > 0){
            $resultCode = -1;
            return $resultCode;
        }
        /*------------------------------------------------------------*/

        /*----------------------- 파일 크기 검사 ----------------------*/
        if($intFileSize > $intFileSizeLimit){
            $resultCode = -2;
            return $resultCode;
        }
        /*------------------------------------------------------------*/

        /*--------------------- 파일 확장자 체크 ---------------------*/
        if($strFileKind=="img"){
            if(!empty($strFileExt) && in_array($strFileExt, $arrayImgExt)!=true){
                $resultCode = -3;
                return $resultCode;
            }
        }else if(!empty($strFileExt) && $strFileKind=="excel"){
            if(in_array($strFileExt, $arrayExcelExt)!=true){
                $resultCode = -3;
                return $resultCode;
            }
        }else{
            if(!empty($strFileExt) && in_array($strFileExt, $arrayAllowExt)!=true){
                return $resultCode = -3;
            }
        }
        /*------------------------------------------------------------*/

        /*-------------------- 파일 MimeType 검사 ---------------------*/
        if($strFileKind=="img"){
            if(!in_array($strFileMime, $arrayImgMime)){
                $resultCode = -4;
                return $resultCode;
            }
        }elseif($strFileKind=="excel"){
            if(!in_array($strFileMime, $arrayExcelMime)){
                $resultCode = -4;
                return $resultCode;
            }
        }else{
            if(!in_array($strFileMime, $arrayAllowMime)){
                $resultCode = -4;
                return $resultCode;
            }
        }
        /*------------------------------------------------------------*/
        return $resultCode;
    }

    /*
        @desc : 파일 업로드
        @param :
            - $fileObj : 파일 오브젝트 $_FILES[INPUT NAME]
            - $fileType : 파일 검증 종류 - img, excel, 그 외(허용가능 파일)
            - $uploadDir : 파일 업로드 할 경로
            - $sizeLimit : 파일 크기 (kb 단위)
        @return :
            - json 리턴
            - 성공일 경우 - resultCode(1: 성공), resultMsg(업로드 성공), fileResult(배열: fileName, fileSize, uploadDir, fileOriginName)
                ex) ['resultCode'=>1, 'resultMsg'=>'업로드 성공', fileResult=>'['fileName=>a.jpg, 'fileSize'=>11111, 'uploadDir'=>/mnt/phpupload, 'fileOriginName'=>a.jpg]']
            - 실패일 경우 - resultCode(0: 실패, -1~-5: 예외처리 ), resultMsg(실패사유), fileResult(null)
                ex) ['resultCode'=>0, 'resultMsg'=>'업로드 실패', fileResult=>null]
            - 코드 및 메세지는 하단 fnGetFileUploadExceiption() 함수 참조
    */
    function fnFileUpload($fileObj, $fileType, $uploadDir, $sizeLimit, $savefileName=''){
        //$uploadDir = '/usr/local/phproot/samplePHP/resources/uploads/'; // 업로드 경로
        //$fileObj = $_FILES['fileInput']; // 파일 오브젝트
        $fileName = $fileObj['name']; // 파일명
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);  // 확장자
        $fileTmpName = $fileObj['tmp_name']; // 임시폴더 업로드 파일명
        $fileSize = $fileObj['size']; // 파일 사이즈

        // 파일명 파라미터가 존재할 경우 파라미터값으로, 없을 경우 랜덤파일명으로 저장
        if(!empty($savefileName)){
            $fileOriginName = $savefileName;
        }else{
            $fileOriginName = date('YmdHis').mt_rand();
        }

        if(!empty($fileExt)){ // 확장자가 있으면 파일명 + .확장자
            $fileOriginName .= '.'. $fileExt;
        }

        $fileMime = strtolower(mime_content_type($fileObj["tmp_name"])); // 파일 MIME 타입
        $uploadFile = $uploadDir . $fileOriginName;
        $resultCode = 1;

        if(($fileObj['error'] > 0) || ($fileSize <= 0)) { // 파일 사이즈가 0이하거나 PHP 파일 관련 에러가 발생할 경우 FileUploadException 으로 throw
            // ※ $fileObj['error'] - 파일업로드 중 생기는 에러를 출력한다.
            $resultCode = 0;
        } else {
            if(!is_uploaded_file($fileTmpName)) { // HTTP post로 전송된 것인지 체크한다.
                $resultCode = -5;
            }else {
                // fnFileUploadCheck(fileObject, fileKind, fileSize(KB));
                $resultCode = fnFileUploadCheck($fileObj, $fileType, $sizeLimit); // 파일 체크 결과 성공 : 'true', 실패 : '실패 관련 메세지'
                if($resultCode===1){
                    /*
                        move_uploaded_file()
                         - PHP는 파일을 임시폴더에 저장하고 있다가 move_uploaded_file() 을 실행할 때 파일을 검증 후 지정한 업로드 폴더로 이동시킨다.
                         - 이동 후 일정 시간 후 삭제한다.
                         1. 임시 저장되어 있는 파일을 $uploadFile 경로로 이동한다.
                         2. 또한 PHP 엔진에서 파일 검증을 해준다.
                    */
                    if(move_uploaded_file($fileTmpName, $uploadFile)) {
                        $fileArray = [];
                        $fileArray = ['fileName'=>$fileName, 'fileSize'=>$fileSize, 'uploadDir'=>$uploadDir, 'fileOriginName'=>$fileOriginName];
                        $resultArray = ['resultCode'=>1, 'resultMsg'=>'업로드 성공', 'fileResult'=>$fileArray];
                        return $resultArray;
                        exit;
                    }else{
                        $resultCode = 0;
                    }
                }
            }
        }
        $resultArray = fnGetFileUploadExceiption($resultCode);
        return $resultArray;
    }

    /*
        일반적인 오류 외에 파일업로드 시 exception 상황
        fnFileUpload() 함수 주석 참조
    */
    function fnGetFileUploadExceiption($resultCode){
        switch ($resultCode) {
            case '0':
                $resultMsg = '파일 업로드를 실패하였습니다.';
                break;
            case '-1':
                $resultMsg = '보안에 위배되는 파일형식 입니다.';
                break;
            case '-2':
                $resultMsg = '업로드 파일이 허용 사이즈 보다 큽니다.';
                break;
            case '-3':
                $resultMsg = '업로드가 허용되지 않는 파일 형식입니다.';
                break;
            case '-4':
                $resultMsg = '업로드가 허용되지 않는 파일 형식입니다.';
                break;
            case '-5':
                $resultMsg = 'HTTP로 전송된 파일이 아닙니다.';
                break;
        }
        return $resultArray = ['resultCode'=>$resultCode, 'resultMsg'=>$resultMsg, 'fileResult'=>null];
    }

    // 업로드하는 파일의 확장자, mimetype, 용량을 확인함
    function fileCheck($file, $extCheck, $sizeCheck) {
        $extValid = [
        'jpg' => 'image/pjpeg,image/jpeg,image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png,image/x-png',
        'doc' => 'application/msword',
        'docx' => 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip' => 'application/x-zip-compressed,application/zip',
        'pdf' => 'application/pdf,application/x-pdf',
        'mp3' => 'audio/mpeg'
        ];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = strtolower(mime_content_type($file['tmp_name']));
        $size = $file['size'];

        if ($file['error'] > 0) {
        return '업로드가 실패되었습니다.';
        }

        if ($size <= 0) {
        return '용량문제로 업로드가 실패되었습니다.';
        }

        // 기본으로 허용된 파일 확장자 확인
        if (!array_key_exists($ext, $extValid)) {
        return '업로드 불가능한 파일입니다.';
        }

        // 기본으로 허용된 mimetype 확인
        if (!in_array($mimeType, explode(',', $extValid[$ext]))) {
        return '업로드 불가능한 파일형식입니다.';
        }

        // 개별적으로 허용된 파일 확장자 확인(mimetype은 위에서 확인했기 때문에 따로 해 줄 필요 없음)
        if ($extCheck != '' && !in_array($ext, explode(',', $extCheck))) {
        return '업로드가 허용되지 않는 파일입니다.';
        }

        // 용량 체크
        if ($size > $sizeCheck) {
        return '업로드 허용용량을 초과하였습니다';
        }

        return true;
    }

    // 업로드되는 파일을 저장함
    //    리턴값으로 배열을 반환함

    function fileSave($file, $extCheck, $sizeCheck, $uploadPath) {
        $result = [
        'result' => false,
        'msg' => ''
        ];

        $check = fileCheck($file, $extCheck, $sizeCheck);

        if ($check === true) {
        $tmpname = $file['tmp_name'];
        // 파일명이 한글일 경우 오류가 있어서 아래처럼 처리해야함
        $filename = urldecode(pathinfo(urlencode($file['name']), PATHINFO_FILENAME));
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $basename = $filename.'.'.$ext;
        $cnt = 1;

        if (!is_uploaded_file($tmpname)) {
            $result['msg'] = 'HTTP로 전송된 파일이 아닙니다.';
            return $result;
        }

        // 파일중복확인하여 고유파일명 생성
        while (file_exists($uploadPath.$basename)) {
            $basename = $filename.'['.($cnt++).'].'.$ext;

            if ($cnt > 100) {
            $result['msg'] = '파일중복확인 오류입니다.';
            return $result;
            }
        }

        if (move_uploaded_file($tmpname, $uploadPath.$basename)) {
            $result['result'] = true;
            $result['msg'] = $basename;
            return $result;
        }
        else {
            $result['msg'] = '파일저장 오류입니다.';
            return $result;
        }
        }
        else {
        $result['msg'] = $check;
        return $result;
        }
    }

 
// 파일다운로드
function fileDownload($uploadPath, $filename, $saveName = '') {
    if ($saveName === '') {
        $uploadFile = $uploadPath . $filename;
    } else {
        $uploadFile = $uploadPath . $saveName;
    }

    if (file_exists($uploadFile)) {
        if (preg_match('(MSIE|Trident)', $_SERVER['HTTP_USER_AGENT'])) {
            Header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            Header('Content-type: application/octet-stream');
            Header('Content-Length: '.filesize($uploadFile));
            Header('Content-Disposition: attachment; filename="'.iconv('utf-8', 'euc-kr', $filename)).'"';
            Header('Content-Transfer-Encoding: binary');
            Header('Pragma: public');
            Header('Expires: 0');
        }
        else {
            Header('Content-type: file/unknown');
            Header('Content-Length: '.filesize($uploadFile));
            Header('Content-Disposition: inline; filename="'.$filename.'"');
            Header('Content-Description: PHP3 Generated Data');
            Header('Pragma: no-cache');
            Header('Expires: 0');
        }

        $file = fopen($uploadFile, 'r');
        if (!fpassthru($file)) {
            fclose($file);
        }
    }
    else {
        fnShowAlertMsg("파일이 없습니다.", "history.back();", true);
    }
}
?>
