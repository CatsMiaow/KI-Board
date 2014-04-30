## Codeigniter & Bootstrap 기반 게시판 소스

* 테스티드 <http://www.tested.co.kr>
* 게시판 샘플 사이트 <http://board.tested.co.kr/board/test>
 * 관리자 정보 test / test <http://board.tested.co.kr/adm>
* Codeigniter 한국사용자포럼 <http://codeigniter-kr.org>
* [변경 내역](CHANGELOG.md) / [설치 방법](INSTALL.md)


## 머리말

PHP Framework Codeigniter(v2.1.4)와 UI Framework Bootstrap(v3.1.0)을 기반으로 제작된 게시판 소스입니다.  
그누보드 구조를 참고하였으며, [샘플 사이트](http://board.tested.co.kr/board/test) 그대로의 모습입니다.  
Codeigniter 관련 내용은 한국사용자포럼의 [한글매뉴얼](http://codeigniter-kr.org/user_guide_2.1.0)을 참고하시기 바랍니다.

오류 등의 기타 문의는 [테스티드](http://www.tested.co.kr)에 남겨주시기 바랍니다.  
__이 게시판을 사용하여 발생하는 피해에 대해서 1원도 책임지지 않습니다.__


## 사용 소스

* [Codeigniter](http://codeigniter.com)
* [Bootstrap](http://getbootstrap.com)
* [jQuery](http://jquery.com)
* [jQuery Validation](http://jqueryvalidation.org)
* [SWFUpload](https://code.google.com/p/swfupload)
* [Daum Open Editor](https://github.com/daumcorp/DaumEditor)


## CI Core 변경 내역

#### 직접 수정

system/core/URI.php - 154 line

    // 원본
    if ( ! isset($_SERVER['REQUEST_URI']))
 
    // 수정
    if ( ! isset($_SERVER['REQUEST_URI']) OR empty($_SERVER['SCRIPT_NAME']))


#### 확장
- app/core/MY_Loader.php
- app/core/MY_Router.php
- app/core/MY_Security.php
- app/libraries/MY_Pagination.php
- app/libraries/MY_Session.php
- app/libraries/MY_Log.php


## 게시판 기능 정리
 
* 회원
 * 회원레벨 1 ~ 10 ( 1:비회원, 2:일반회원, 10:관리자 )
 * 회원아이콘( 이름 앞에 아이콘 ), 이미지이름( 이름 대신 아이콘 )
 * 회원가입 이메일 인증
 * 나머지는 회원 등록 시 확인
 
* 게시판
 * 다음( daum ) 에디터 적용
 * 그룹별 게시판 관리
 * 다중 분류 기능( 분류 사용 체크 시 게시판 수정에서 아이콘 확인 )
 * 여분 필드 생성( 여분필드 사용 체크 시 게시판 수정에서 아이콘 확인 ), extra 게시판 스킨 참고
 * 게시글 SNS 보내기 링크
 * SyntaxHighlighter( 코드 구문 강조 )
 * 개인 게시판 관리자 설정 기능( 게시판 관리자가 게시판 리스트에서 관리자 버튼으로 확인 )
 * 나머지는 게시판 등록 시 확인
 
* 쪽지
 * http://board.tested.co.kr/member/memo/lists
 * 보내기/받기 단순 구현, 쪽지 확인 여부 표시
 
* 포인트
 * 관리자 > 회원 > 포인트관리
 * <http://board.tested.co.kr/member/point>
 * 회원가입/로그인 시 지급, 나머지는 프로그램되어 있지 않음
 
* 메일
 * 관리자 > 회원 > 회원메일발송
 * PHP 함수 메일 발송 기능, 회원 검색 지원
 
* 팝업
 * 관리자 > 기타 > 팝업관리
 * 팝업 등록 시 확인

