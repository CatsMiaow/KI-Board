## 변경 내역( Change Log )

#### v1.0 [ 2011.08.19 ]

* Codeigniter 2.0.2 버전 업그레이드
 * <http://ellislab.com/codeigniter/user-guide/changelog.html>


#### v1.1 [ 2011.11.07 ]

* Codeigniter 2.0.3 버전 업그레이드
 * http://codeigniter.com/user_guide/installation/upgrade_203.html
* 회원정보수정의 별명/이메일 유효성 검사 오류 수정
* http://campaign.naver.com/goodbye-ie6


#### v1.2 [ 2012.01.12 ]

* Codeigniter 2.1.0 버전 업그레이드
 * <http://ellislab.com/codeigniter/user_guide/installation/upgrade_210.html>
* DaumEditor v7.2.1 버전 업그레이드
* 게시글 SNS 보내기 링크 추가
* 팝업관리 기능 추가
 * cf\_basic.php 설정에 cf\_use\_popup 추가
* 이메일 유효성 점검( *.co.kr )


#### v1.3 [ 2012.01.17 ]

* 검색 파트( $spt ) 점검
 * cf\_board.php 설정에 cf\_search\_part 추가
* ki\_write 테이블 index 개선


#### [ 2012.07.03 ]

* 관리자에서 환경설정 확인 페이지 추가
* 타 브라우저 줄바꿈 문제 수정
* 5차 분류 리스트 출력 문제 수정

---

#### v2.0 [ 2012.08.18 ]

* __테이블 구조가 변경되었기 때문에 버전을 분리__
* 댓글 테이블 분리( ki\_comment, 댓글 검색 삭제 )
* 댓글 페이징( bo\_page\_rows\_comt, 단순 페이징 )
* Codeigniter 2.1.2 버전 패치
* DaumEditor 7.3.21 버전 패치


#### v2.1 [ 2013.01.14 ]

* <http://www.tested.co.kr/board/Study/view/wr_id/44>
* 댓글이 없을 때 댓글 박스가 출력되지 않는 문제 수정( /_board/comment.php )
* 댓글 테이블 분리 후 통합 검색 오류 수정( /search, 검색구분에서 댓글 선택 가능 )
* Codeigniter 2.1.3 버전 패치
* DaumEditor 7.3.29 버전 패치


#### v2.2 [ 2013.05.11 ]

* __※ UTF8로 인코딩 변경, EUC-KR은 지원하지 않음__
* 아이디로 검색, 이름으로 검색 링크 오류 수정
* 게시글 작성 시 SyntaxHighlighter( 코드 구문 강조 ) 기능 추가
* DaumEditor 7.3.39 버전 패치
* jQuery 1.9.1 버전 패치
* jQuery Plugin: Validation 1.11.1 버전 패치


#### v2.3 [ 2013.08.04 ]

* __Bootstrap 3 RC1 기반 UI 디자인__( <http://getbootstrap.com> )
 * 최대한 커스텀 CSS, 이미지 파일이 없도록 수정
* 개인 게시판 관리자 설정 기능 추가
 * 게시판 관리자로 설정된 유저가 게시판 리스트에서 관리자 버튼으로 확인
* Codeigniter 2.1.4 패치, DaumEditor 7.3.45 패치
* Search\_helper를 Segment\_library로 변경
* 주민번호 시스템 삭제( mb\_jumin 삭제 )
* j.\* → jquery.\* 파일 이름 변경, 파일명이 명확하지 않음
* SyntaxHighlighter 구문 오류 수정
* Syntax 사용 시 리스트 스크립트 오류 수정


#### v2.4 [ 2013.09.16 ]

* Bootstrap v3.0.0 적용( <http://getbootstrap.com/getting-started/#migration> )
* 검색엔진 최적화 문제로 검색 파라미터를 세그먼트에서 쿼리스트링으로 변경
 * 전) <http://board.tested.co.kr/board/test/lists/sfl/wr_subject/stx/searchText/page/2>
 * 후) <http://board.tested.co.kr/board/test/lists/page/2?sfl=wr_subject&stx=searchText>
* 검색 파라미터 라이브러리 추가/수정
 * libraries/Segment.php → 세그먼트 관리
 * libraries/Querystring.php → 쿼리스트링 관리 
* jQuery library 파일 이동
 * js/jquery.validate.js → js/jquery/validate.js
* 컨트롤러가 아닌( Ajax, Post ) 파일의 폴더 구분
 * app/controllers/\_trans/\*
 * app/controllers/adm/\_trans/\*
* models 폴더의 파일을 prefix m\_\* 을 suffix \*\_model 으로 변경
 * m\_basic.php → basic\_model.php
* 외부 로그인 정리( 'outlogin' => widget::run('member/outlogin') )
* 여분필드 사용 스킨 정리( <http://board.tested.co.kr/board/extra> )


#### v2.5 [ 2013.12.18 ]

* jQuery Validation Plugin에 Bootstrap Tooltip 적용
* Checkbox, Radio 버튼을 Bootstrap 버튼으로 구현
* 레이어 메시지 함수( alertMsg ) 추가
* $list[$i] = new stdClass(); // PHP 5.4에서 발생하는 오류 수정
* 이전글, 다음글 링크 오류 수정
* 통합검색 링크 오류 수정
* 1차 분류 Key가 2자리일 때 하위분류 출력 오류 수정
* 다음 에디터 로드 로직 개선

