=== 2D Tag Cloud by Sujin ===
Contributors: sujin2f
Donate link: http://www.sujinc.com/lab/2d-tag-cloud-widget/
Tags: tag, cloud
Requires at least: 2.8
Tested up to: 3.5
Stable tag: 4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

"2D Tag Cloud by Sujin" is one of the Wordpress widget, which makes tag-cloud with two visual value.

== Description ==

"2D Tag Cloud by Sujin" is one of the Wordpress widget, which makes tag-cloud with two visual value.
For example, size means that how many times have clicked the tag, and color means how many posts has had the tag.
This is very simple way to display your tag-cloud more pretty and more highly decorative following the order.
You can select color and size on admin page.

2D 태그 클라우드는 두가자의 기준에 의해 글자의 색상과 크기를 달리해서 태그를 표시하는 플러그인이에요.
많이 클릭된 태그와 많이 포함된 태그, 이 두 가지 기준에 의해 한 태그는 각각 다른 색상과 크기를 가지죠.
어떻게 정렬하는지에 따라 매우 불규칙한 모양이 형성될 수 있어요.
색상과 크기는 어드민에서 지정해주세요~ :)

You can use shortcode also. See Frequently Asked Questions.

숏코드를 사용해서 표시를 해줄 수도 있어요. Frequently Asked Questions을 봐주세요~

Official Page : <a href="http://www.sujinc.com/lab/2d-tag-cloud-widget/">2D Tag Cloud by Sujin</a>

Running Sample : <a href="http://www.sujinc.com/">Footer of sujinc.com / 제 블로그의 푸터에서 보실 수 있어요.</a>

== Installation ==

= Installation =
1. Download the plugin zip package and extract it (플러그인을 다운로드하여 압축을 해제해주세요)
1. Put the folder named "two-dimensional-tag-cloud-sujin" under /wp-content/plugins/ directory (플러그인 디렉토리에 넣어주세요)
1. Go to the plugins page in your Wordpress admin panel and click "Activate" (플러그인 관리 페이지에서 활성화 시키세요)

= Global Setting =
1. Go to "2D Tag Cloud" under the "Settings" (세팅의 "2D Tag Cloud"로 이동해주세요)
1. Set Tag Step and Method (Step과 Method를 설정해주세요)
1. Changing the step selector, you'll see the step marker appearing or disappearing (Step을 바꾸시면 표가 늘었다 줄었다 하는 걸 보실 수 있어요)
1. On step marker, set custom color and size (색상과 크기를 설정하세요)
   * Higher-level step means more valued (View / Including) (높은 숫자의 Step의 스타일대로 많이 포함되거나 많이 클릭된 태그가 표시됩니다)
1. Click "Make Preview" to preview your tag cloud. (Make Preview 버튼을 클릭하셔서 미리보기를 하실 수 있어요)

= Widget Setting =
1. Go to "Widgets" under the "Appearance" menu (위젯 메뉴로 가세요)
1. Drag "2D Tag Cloud Widget by Sujin" to the widget holder you want to set (위젯을 끌어다 놓습니다)
1. Set title, quantity and separator (타이틀과 태그의 수량과 구분자를 입력하실 수 있어요)
1. If you change sort setting, you will get a different shape of tag cloud. (Sort를 변경해서 태그의 정렬 방법을 결정하실 수 있어요)

NOTE : At the early stage of applying this plugin, you cannot perceive a property of View step on your tag-cloud.
When clicked the tag or displayed the post which has the tag, the View Counts will be increased.

주의 : 플러그인을 설치하신 딱! 고 당시에는 클릭 수로 설정된 요소(크기나 색상)가 모두 똑같을 거에요. 워드프레스는 포스트의 View를 세거나 하는 기능이 없어요.
많은 사람들이 여러분의 블로그를 방문해서 태그를 클릭하거나 태그가 포함된 포스트를 보면 따로 설정한 카운트가 올라가면서 그 요소가 변경될 거에요~ :)

== Frequently Asked Questions ==

= Will this plugin replace previous tag cloud? (이전 태그 클라우드가 변경되나요?) =

No. You have to put our widget to your widget holder. (아뇨. 위젯 홀더에 따로 준비된 위젯을 넣어주세요.)

== Shortcode ==

You can use a shortcode to display a tag-cloud in your post/page or .php files.
shortcode is [tag2d] you can controll with 3 attributes.

* number : Number of tags to show. You MUST set numeric value.
* separator : Put separator in between each tag.
* set : Put set name.
* sort : You can set a sort to one of those three value.

 - intersection : This plugin sort tags on 2-way (Click count and Having count). And put tags cross by different order (Bigger, Smaller, Bigger, Smaller...)

 - DESC : Same as 'intersection', but put tags by descending order (Bigger, Bigger, Bigger, Bigger...)

 - name : Put tags by name.

== Shortcode (Kor) ==

숏코드를 사용해서 태그 클라우드를 표시할 수도 있어요. Post나 Page 혹은 php 파일을 편집해서 2D 태그를 구할 수 있죠.
숏코드는 [tag2d] 형식이구요. 세 개의 인자를 가집니다. (안 써도 상관 없어요)

* number : 태그의 표시 수에요.
* separator : 태그 사이에 넣을 구분자죠.
* set : 저장한 세팅 값을 넣어주세요.
* sort : 아래 세 개의 값 중 하나를 넣으세요.

 - intersection : 크고 작은 값을 하나씩 교차해서 표시해요. 포함 많은 것, 클릭 적은 것, 포함 그 다음으로 많은 것, 클릭 그 다음으로 적은 것...

 - DESC : 큰 값을 먼저 표시해요. 포함 많은 것, 클릭 많은 것, 포함 많은 것, 클릭 많은 것...

 - name : 이름에 따라 정렬합니다.


== Screenshots ==

1. Widget Setting / 위젯 세팅
2. Global Setting Page / 전역 세팅
3. Result / 결과

== Changelog ==

= 4.0 =
* You can set mouse-over color. (마우스 오버 시 컬러 변화를 설정할 수 있어요)
* If you set mouse-over color, I want you to set padding and border-radius. (마우스 오버 컬러를 설정하시면 패딩하고 보더 래디어스를 설정하시길 바래요)
* You can set text-underline attribute when mouse-over. (마우스 오버 시 언더라인 표시 여부도 설정하실 수 있어요.)

= 3.0.1 =
* Fix bugs that caused with older version of WP. (옛날 버전의 WP에서 나오는 오류를 수정했어요.)

= 3.0 =
* Convert functions to class. It will prevent a duplicate error. (클래스 기반으로 변경했어요. 에러를 없애고 코드를 다이어트 했죠.)
* Support multi-language. (다국어 추가했어요.)

= 2.8 =
* Fix Critical Bug. You MUST Update! (2.7에서 업데이트 해주세요!)
* Add a function, which you can save your setting as separate set. (세팅을 따로 저장할 수 있어요~)

= 2.7 =
* Set style using css file, not a inline-style. So the style in this plugin will ignore global css. (인라인 스타일이 아닌 CSS를 따로 빼서 설정해요. a 태그 같은 경우엔 때론 전역 세팅이 붙기도 해서요.)
* Add Korean to readme.txt file. (readme.txt에 한국어를 추가했어요. 전 참 대단한 애국자에요)
* You can use a shortcode in your post/page or php file. (숏코드를 추가했어요.)

= 2.6 =
* Fix option-saving bug with some system.

= 2.5 =
* Make sort setting on widget section.

= 2.0 =
* Fix some bugs.
* Add more presetting.
* Add line-height and margin setting.
* Change setting view to table shape.
* Add preview.
* Add id and class key of tags. So you can make custom css.
* Make to run at least Wordpress 2.8 version.

= 1.1.1 =
* Fix tag's output style (Add 'display:inline-block', line-height and margin). On next update, I'll put that function on user setting.

= 1.1 =
* Add preset config

= 1.0 =
* Original Version