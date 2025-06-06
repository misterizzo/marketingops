(()=>{"use strict";var e={n:t=>{var a=t&&t.__esModule?()=>t.default:()=>t;return e.d(a,{a}),a},d:(t,a)=>{for(var l in a)e.o(a,l)&&!e.o(t,l)&&Object.defineProperty(t,l,{enumerable:!0,get:a[l]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.element,a=window.wp.i18n,l=window.wp.blocks,r=window.wp.blockEditor,s=window.wp.components,n=window.wp.serverSideRender;var o=e.n(n);const i="learndash/ld-login",d=(0,a.__)("LearnDash Login","learndash");(0,l.registerBlockType)(i,{title:d,description:(0,a.__)("This block adds the login button on any page","learndash"),icon:"admin-network",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{login_url:{type:"string",default:""},login_label:{type:"string",default:""},login_placement:{type:"string",default:""},login_button:{type:"string",default:""},logout_url:{type:"string",default:""},logout_label:{type:"string",default:""},logout_placement:{type:"string",default:"right"},logout_button:{type:"string",default:""},preview_show:{type:"boolean",default:!0},preview_action:{type:"string",default:""},example_show:{type:"boolean",default:0}},edit:function(e){const{attributes:{login_url:l,login_label:n,login_placement:u,login_button:c,logout_url:p,logout_label:_,logout_placement:h,logout_button:g,preview_show:m,preview_action:b,example_show:y},setAttributes:f}=e,w=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Login Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.__)("Login URL","learndash"),help:(0,a.__)("Override default login URL","learndash"),value:l||"",onChange:e=>f({login_url:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Login Label","learndash"),help:(0,a.__)('Override default label "Login"',"learndash"),value:n||"",onChange:e=>f({login_label:e})}),(0,t.createElement)(s.SelectControl,{key:"login_placement",label:(0,a.__)("Login Icon Placement","learndash"),value:u,options:[{label:(0,a.__)("Left - To left of label","learndash"),value:""},{label:(0,a.__)("Right - To right of label","learndash"),value:"right"},{label:(0,a.__)("None - No icon","learndash"),value:"none"}],onChange:e=>f({login_placement:e})}),(0,t.createElement)(s.SelectControl,{key:"login_button",label:(0,a.__)("Login Displayed as","learndash"),help:(0,a.__)("Display as Button or link","learndash"),value:c,options:[{label:(0,a.__)("Button","learndash"),value:""},{label:(0,a.__)("Link","learndash"),value:"link"}],onChange:e=>f({login_button:e})})),v=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Logout Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.__)("Logout URL","learndash"),help:(0,a.__)("Override default logout URL","learndash"),value:p||"",onChange:e=>f({logout_url:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Logout Label","learndash"),help:(0,a.__)('Override default label "Logout"',"learndash"),value:_||"",onChange:e=>f({logout_label:e})}),(0,t.createElement)(s.SelectControl,{key:"logout_placement",label:(0,a.__)("Logout Icon Placement","learndash"),value:h,options:[{label:(0,a.__)("Left - To left of label","learndash"),value:"left"},{label:(0,a.__)("Right - To right of label","learndash"),value:"right"},{label:(0,a.__)("None - No icon","learndash"),value:"none"}],onChange:e=>f({logout_placement:e})}),(0,t.createElement)(s.SelectControl,{key:"logout_button",label:(0,a.__)("Logout Displayed as","learndash"),help:(0,a.__)("Display as Button or link","learndash"),value:g,options:[{label:(0,a.__)("Button","learndash"),value:""},{label:(0,a.__)("Link","learndash"),value:"link"}],onChange:e=>f({logout_button:e})})),C=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!m,onChange:e=>f({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.SelectControl,{key:"preview_action",label:(0,a.__)("Preview Action","learndash"),value:b,options:[{label:(0,a.__)("Login","learndash"),value:"login"},{label:(0,a.__)("Logout","learndash"),value:"logout"}],onChange:e=>f({preview_action:e})}));function E(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),d)}function x(e){return E()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},w,v,C),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(0,t.createElement)(o(),{block:i,attributes:a,key:i,EmptyResponsePlaceholder:x}):E();var a}),[e.attributes])]},save:e=>{}}),window.learndash=window.learndash||{},learndash.customLabel=learndash.customLabel||{},learndash.customLabel={get:function(e=""){return void 0!==ldlms_settings.meta.post&&""!==e&&void 0!==ldlms_settings.settings.custom_labels[e]?ldlms_settings.settings.custom_labels[e]:e},getLower:function(e=""){return void 0!==ldlms_settings.meta.post&&""!==e&&void 0!==ldlms_settings.settings.custom_labels[e+"_lower"]?ldlms_settings.settings.custom_labels[e+"_lower"]:e},getSlug:function(e=""){return""!==e&&void 0!==ldlms_settings.settings.custom_labels[e+"_slug"]?ldlms_settings.settings.custom_labels[e+"_slug"]:e}};const u=learndash.customLabel.get;function c(e=""){return""!==e&&void 0!==ldlms_settings.meta.post[e]?ldlms_settings.meta.post[e]:void 0!==ldlms_settings.meta.post?ldlms_settings.meta.post:void 0}function p(e=""){if(""!==e){if(void 0!==ldlms_settings.settings.per_page[e])return ldlms_settings.settings.per_page[e]}else if(void 0!==ldlms_settings.meta.posts_per_page)return ldlms_settings.meta.posts_per_page}function _(e){if(void 0===e)return 0;const t=parseInt(e);return isNaN(t)?0:t}function h(){return void 0!==ldlms_settings.templates.active?ldlms_settings.templates.active:""}function g(){let e=function(){if("legacy"==h()){let e=function(){if(void 0!==ldlms_settings.templates.list){let e=h();if(void 0!==e&&""!==e&&void 0!==ldlms_settings.templates.list[e])return ldlms_settings.templates.list[e]}return""}();return sprintf(
// translators: placeholder: current template name.
// translators: placeholder: current template name.
(0,a._x)('The current LearnDash template "%s" does not support this block. Please select a different template.',"placeholder: current template name","learndash"),e)}return""}();return""!==e?(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Warning","learndash"),opened:!0},(0,t.createElement)(s.TextControl,{help:e,value:"",type:"hidden",className:"notice notice-error"})):""}learndash.customLabel.getLower,learndash.customLabel.getSlug;const m="learndash/ld-profile",b=(0,a.__)("LearnDash Profile","learndash");(0,l.registerBlockType)(m,{title:b,description:(0,a.sprintf)(
// translators: placeholders: Courses, Course, Quiz.
// translators: placeholders: Courses, Course, Quiz.
(0,a._x)("Displays user's enrolled %1$s, %2$s progress, %3$s scores, and achieved certificates.","placeholders: Courses, Course, Quiz","learndash"),u("courses"),u("course"),u("quiz")),icon:"id-alt",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{per_page:{type:"string",default:""},orderby:{type:"string",default:"ID"},order:{type:"string",default:"DESC"},course_points_user:{type:"boolean",default:1},expand_all:{type:"boolean",default:0},profile_link:{type:"boolean",default:1},show_header:{type:"boolean",default:1},show_search:{type:"boolean",default:1},show_quizzes:{type:"boolean",default:1},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},quiz_num:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{per_page:l,orderby:n,order:i,course_points_user:d,expand_all:_,profile_link:h,show_header:g,show_search:y,show_quizzes:f,preview_user_id:w,preview_show:v,quiz_num:C,example_show:E},setAttributes:x}=e,k=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("%s per page","placeholder: Courses","learndash"),u("courses")),help:(0,a.sprintf)(
// translators: placeholder: per_page.
// translators: placeholder: per_page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: per_page","learndash"),p("per_page")),value:l||"",type:"number",onChange:function(e){x(""!=e&&e<0?{per_page:"0"}:{per_page:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholders: Quiz, Course.
// translators: placeholders: Quiz, Course.
(0,a._x)("%1$s attempts per %2$s","placeholders: Quiz, Course","learndash"),u("quiz"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: per_page.
// translators: placeholder: per_page.
(0,a._x)("Leave empty for default (%d) or 0 to show all attempts.","placeholder: per_page","learndash"),p("per_page")),value:C||"",type:"number",onChange:function(e){x(""!=e&&e<0?{quiz_num:"0"}:{quiz_num:e})}}),(0,t.createElement)(s.SelectControl,{key:"orderby",label:(0,a.__)("Order by","learndash"),value:n,options:[{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>x({orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"order",label:(0,a.__)("Order","learndash"),value:i,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>x({order:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Search","learndash"),checked:!!y,onChange:e=>x({show_search:e}),help:(0,a.__)("LD30 template only","learndash")}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Profile Header","learndash"),checked:!!g,onChange:e=>x({show_header:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Show Earned %s Points","placeholder: Course","learndash"),u("course")),checked:!!d,onChange:e=>x({course_points_user:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Profile Link","learndash"),checked:!!h,onChange:e=>x({profile_link:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("Show User %s Attempts","placeholder: Quiz","learndash"),u("quiz")),checked:!!f,onChange:e=>x({show_quizzes:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Expand All %s Sections","placeholder: Course","learndash"),u("course")),checked:!!_,onChange:e=>x({expand_all:e})})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!v,onChange:e=>x({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:w||"",type:"number",onChange:function(e){x(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function T(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),b)}function P(e){return T()}return[k,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:m,attributes:a,key:m,EmptyResponsePlaceholder:P})):T();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const y="learndash/ld-course-list",f=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s List","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(y,{title:f,description:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("This block shows a list of %s.","placeholder: Courses","learndash"),u("courses")),icon:"list-view",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{orderby:{type:"string",default:"ID"},order:{type:"string",default:"DESC"},per_page:{type:"string",default:""},mycourses:{type:"string",default:""},status:{type:"array",default:["not_started","in_progress","completed"]},show_content:{type:"boolean",default:!0},show_thumbnail:{type:"boolean",default:!0},course_category_name:{type:"string",default:""},course_cat:{type:"string",default:""},course_categoryselector:{type:"boolean",default:!1},course_tag:{type:"string",default:""},course_tag_id:{type:"string",default:""},category_name:{type:"string",default:""},cat:{type:"string",default:""},categoryselector:{type:"boolean",default:!1},tag:{type:"string",default:""},tag_id:{type:"string",default:""},course_grid:{type:"boolean"},progress_bar:{type:"boolean",default:!1},col:{type:"integer",default:ldlms_settings.plugins["learndash-course-grid"].col_default||3},price_type:{type:"array",default:["open","free","paynow","subscribe","closed"]},preview_show:{type:"boolean",default:!0},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{orderby:l,order:n,per_page:i,mycourses:d,status:_,show_content:h,show_thumbnail:g,course_category_name:m,course_cat:b,course_categoryselector:w,course_tag:v,course_tag_id:C,category_name:E,cat:x,categoryselector:k,tag:T,tag_id:P,course_grid:D,progress_bar:I,col:S,preview_user_id:z,preview_show:q,example_show:B,price_type:L},setAttributes:O}=e;let N="",U="",A="",$=!0;if(!0===ldlms_settings.plugins["learndash-course-grid"].enabled){void 0===D||1!=D&&0!=D||($=D);let e=!1;1==$&&(e=!0),A=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Grid Settings","learndash"),initialOpen:e},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Grid","learndash"),checked:!!$,onChange:e=>O({course_grid:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Progress Bar","learndash"),checked:!!I,onChange:e=>O({progress_bar:e})}),(0,t.createElement)(s.RangeControl,{label:(0,a.__)("Columns","learndash"),value:S||ldlms_settings.plugins["learndash-course-grid"].col_default,min:1,max:ldlms_settings.plugins["learndash-course-grid"].col_max,step:1,onChange:e=>O({col:e})}))}N=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Content","learndash"),checked:!!h,onChange:e=>O({show_content:e})}),U=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Thumbnail","learndash"),checked:!!g,onChange:e=>O({show_thumbnail:e})});const G=(0,t.createElement)(s.PanelBody,{className:"learndash-block-controls-panel learndash-block-controls-panel-ld-course-list",title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.SelectControl,{key:"orderby",label:(0,a.__)("Order by","learndash"),value:l,options:[{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>O({orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"order",label:(0,a.__)("Order","learndash"),value:n,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>O({order:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("%s per page","placeholder: Courses","learndash"),u("courses")),help:(0,a.sprintf)(
// translators: placeholder: default per page.
// translators: placeholder: default per page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: default per page","learndash"),p("per_page")),value:i||"",type:"number",onChange:function(e){O(""!=e&&e<0?{per_page:"0"}:{per_page:e})}}),(0,t.createElement)(s.SelectControl,{multiple:!0,key:"price_type",label:(0,a.sprintf)(
// translators: placeholder: Course Access Mode(s).
// translators: placeholder: Course Access Mode(s).
(0,a._x)("%s Access Mode(s)","placeholder: Course Access Mode(s)","learndash"),u("course")),help:(0,a.__)("Ctrl+click to deselect selected items.","learndash"),value:L,options:[{label:(0,a.__)("Open","learndash"),value:"open"},{label:(0,a.__)("Free","learndash"),value:"free"},{label:(0,a.__)("Buy Now","learndash"),value:"paynow"},{label:(0,a.__)("Recurring","learndash"),value:"subscribe"},{label:(0,a.__)("Closed","learndash"),value:"closed"}],onChange:e=>O({price_type:e})}),(0,t.createElement)(s.SelectControl,{key:"mycourses",label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("My %s","placeholder: Courses","learndash"),u("courses")),value:d,options:[{label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("Show All %s (default)","placeholder: Courses","learndash"),u("courses")),value:""},{label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("Show Enrolled %s only","placeholder: Courses","learndash"),u("courses")),value:"enrolled"},{label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("Show not-Enrolled %s only","placeholder: Courses","learndash"),u("courses")),value:"not-enrolled"}],onChange:e=>O({mycourses:e})}),"enrolled"===d&&(0,t.createElement)(s.SelectControl,{multiple:!0,key:"status",label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("Enrolled %s Status","placeholder: Courses","learndash"),u("courses")),help:(0,a.__)("Ctrl+click to deselect selected items.","learndash"),value:_,options:[{label:(0,a.__)("Not Started","learndash"),value:"not_started"},{label:(0,a.__)("In Progress","learndash"),value:"in_progress"},{label:(0,a.__)("Completed","learndash"),value:"completed"}],onChange:e=>O({status:e})}),N,U);let R="";if("yes"===ldlms_settings.settings.courses_taxonomies.ld_course_category){let e=!1;""==m&&""==b||(e=!0),R=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Category Settings","placeholder: Course","learndash"),u("course")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Category Slug","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned category slug.","placeholder: Courses","learndash"),u("courses")),value:m||"",onChange:e=>O({course_category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Category ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Courses","learndash"),u("courses")),value:b||"",type:"number",onChange:function(e){O(""!=e&&e<0?{course_cat:"0"}:{course_cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Category Selector","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows a %s category dropdown.","placeholder: Courses","learndash"),u("courses")),checked:!!w,onChange:e=>O({course_categoryselector:e})}))}let Q="";if("yes"===ldlms_settings.settings.courses_taxonomies.ld_course_tag){let e=!1;""==v&&""==C||(e=!0),Q=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Tag Settings","placeholder: Course","learndash"),u("course")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Tag Slug","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned tag slug.","placeholder: Courses","learndash"),u("courses")),value:v||"",onChange:e=>O({course_tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Tag ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned tag ID.","placeholder: Courses","learndash"),u("courses")),value:C||"",type:"number",onChange:function(e){O(""!=e&&e<0?{course_tag_id:"0"}:{course_tag_id:e})}}))}let M="";if("yes"===ldlms_settings.settings.courses_taxonomies.wp_post_category){let e=!1;""==E&&""==x||(e=!0),M=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Category Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Category Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned WP Category slug.","placeholder: Courses","learndash"),u("courses")),value:E||"",onChange:e=>O({category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Category ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Courses","learndash"),u("courses")),value:x||"",type:"number",onChange:function(e){O(""!=e&&e<0?{cat:"0"}:{cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("WP Category Selector","learndash"),help:(0,a.__)("shows a WP category dropdown.","learndash"),checked:!!k,onChange:e=>O({categoryselector:e})}))}let W="";if("yes"===ldlms_settings.settings.courses_taxonomies.wp_post_tag){let e=!1;""==T&&""==P||(e=!0),W=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Tag Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned WP tag slug.","placeholder: Courses","learndash"),u("courses")),value:T||"",onChange:e=>O({tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("shows %s with mentioned WP tag ID.","placeholder: Courses","learndash"),u("courses")),value:P||"",type:"number",onChange:function(e){O(""!=e&&e<0?{tag_id:"0"}:{tag_id:e})}}))}const j=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!q,onChange:e=>O({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:z||"",type:"number",onChange:function(e){O(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function F(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),f)}function V(e){return F()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},G,A,R,Q,M,W,j),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:y,attributes:a,key:y,EmptyResponsePlaceholder:V})):F();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const w="learndash/ld-lesson-list",v=(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("LearnDash %s List","placeholder: Lesson","learndash"),u("lesson"));(0,l.registerBlockType)(w,{title:v,description:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("This block shows a list of %s.","placeholder: Lessons","learndash"),u("lessons")),icon:"list-view",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{orderby:{type:"string",default:"ID"},order:{type:"string",default:"DESC"},per_page:{type:"string",default:""},course_id:{type:"string",default:""},show_content:{type:"boolean",default:!0},show_thumbnail:{type:"boolean",default:!0},lesson_category_name:{type:"string",default:""},lesson_cat:{type:"string",default:""},lesson_categoryselector:{type:"boolean",default:!1},lesson_tag:{type:"string",default:""},lesson_tag_id:{type:"string",default:""},category_name:{type:"string",default:""},cat:{type:"string",default:""},categoryselector:{type:"boolean",default:!1},tag:{type:"string",default:""},tag_id:{type:"string",default:""},course_grid:{type:"boolean"},col:{type:"integer",default:ldlms_settings.plugins["learndash-course-grid"].enabled.col_default||3},preview_show:{type:"boolean",default:!0},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{orderby:l,order:n,per_page:i,course_id:d,show_content:_,show_thumbnail:h,lesson_category_name:g,lesson_cat:m,lesson_categoryselector:b,lesson_tag:y,lesson_tag_id:f,category_name:C,cat:E,categoryselector:x,tag:k,tag_id:T,course_grid:P,col:D,preview_show:I,preview_user_id:S,example_show:z},setAttributes:q}=e;let B="",L="",O="",N=!0;if(!0===ldlms_settings.plugins["learndash-course-grid"].enabled){void 0===P||1!=P&&0!=P||(N=P);let e=!1;1==N&&(e=!0),O=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Grid Settings","learndash"),initialOpen:e},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Grid","learndash"),checked:!!N,onChange:e=>q({course_grid:e})}),(0,t.createElement)(s.RangeControl,{label:(0,a.__)("Columns","learndash"),value:D||ldlms_settings.plugins["learndash-course-grid"].enabled.col_default,min:1,max:ldlms_settings.plugins["learndash-course-grid"].enabled.col_max,step:1,onChange:e=>q({col:e})}))}B=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Content","learndash"),checked:!!_,onChange:e=>q({show_content:e})}),L=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Thumbnail","learndash"),checked:!!h,onChange:e=>q({show_thumbnail:e})});const U=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID to limit listing. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:d||"",type:"number",onChange:function(e){q(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.SelectControl,{key:"orderby",label:(0,a.__)("Order by","learndash"),value:l,options:[{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>q({orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"order",label:(0,a.__)("Order","learndash"),value:n,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>q({order:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("%s per page","placeholder: Lessons","learndash"),u("lessons")),help:(0,a.sprintf)(
// translators: placeholder: per_page.
// translators: placeholder: per_page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: per_page","learndash"),p("per_page")),value:i||"",type:"number",onChange:function(e){q(""!=e&&e<0?{per_page:"0"}:{per_page:e})}}),B,L);let A="";if("yes"===ldlms_settings.settings.lessons_taxonomies.ld_lesson_category){let e=!1;""==g&&""==m||(e=!0),A=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Category Settings","placeholder: Lesson","learndash"),u("lesson")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Category Slug","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned category slug.","placeholder: Lessons","learndash"),u("lessons")),value:g||"",onChange:e=>q({lesson_category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Category ID","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Lessons","learndash"),u("lessons")),value:m||"",type:"number",onChange:function(e){q(""!=e&&e<0?{lesson_cat:"0"}:{lesson_cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Category Selector","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows a %s category dropdown.","placeholder: Lessons","learndash"),u("lessons")),checked:!!b,onChange:e=>q({lesson_categoryselector:e})}))}let $="";if("yes"===ldlms_settings.settings.lessons_taxonomies.ld_lesson_tag){let e=!1;""==y&&""==f||(e=!0),$=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Tag Settings","placeholder: Lesson","learndash"),u("lesson")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Tag Slug","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned tag slug.","placeholder: Lessons","learndash"),u("lessons")),value:y||"",onChange:e=>q({lesson_tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Tag ID","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned tag ID.","placeholder: Lessons","learndash"),u("lessons")),value:f||"",type:"number",onChange:function(e){q(""!=e&&e<0?{lesson_tag_id:"0"}:{lesson_tag_id:e})}}))}let G="";if("yes"===ldlms_settings.settings.lessons_taxonomies.wp_post_category){let e=!1;""==C&&""==E||(e=!0),G=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Category Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Category Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned WP Category slug.","placeholder: Lessons","learndash"),u("lessons")),value:C||"",onChange:e=>q({category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s Category ID","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Lessons","learndash"),u("lessons")),value:E||"",type:"number",onChange:function(e){q(""!=e&&e<0?{cat:"0"}:{cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("WP Category Selector","learndash"),help:(0,a.__)("shows a WP category dropdown.","learndash"),checked:!!x,onChange:e=>q({categoryselector:e})}))}let R="";if("yes"===ldlms_settings.settings.lessons_taxonomies.wp_post_tag){let e=!1;""==k&&""==T||(e=!0),R=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Tag Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned WP tag slug.","placeholder: Lessons","learndash"),u("lessons")),value:k||"",onChange:e=>q({tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Lessons.
// translators: placeholder: Lessons.
(0,a._x)("shows %s with mentioned WP tag ID.","placeholder: Lessons","learndash"),u("lessons")),value:T||"",type:"number",onChange:function(e){q(""!=e&&e<0?{tag_id:"0"}:{tag_id:e})}}))}const Q=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!I,onChange:e=>q({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:S||"",type:"number",onChange:function(e){q(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function M(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),v)}function W(e){return M()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},U,O,A,$,G,R,Q),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:w,attributes:a,key:w,EmptyResponsePlaceholder:W})):M();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const C="learndash/ld-topic-list",E=(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("LearnDash %s List","placeholder: Topic","learndash"),u("topic"));(0,l.registerBlockType)(C,{title:E,description:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("This block shows a list of %s.","placeholder: Topics","learndash"),u("topics")),icon:"list-view",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{orderby:{type:"string",default:"ID"},order:{type:"string",default:"DESC"},per_page:{type:"string",default:""},course_id:{type:"string",default:""},lesson_id:{type:"string",default:""},show_content:{type:"boolean",default:!0},show_thumbnail:{type:"boolean",default:!0},topic_category_name:{type:"string",default:""},topic_cat:{type:"string",default:""},topic_categoryselector:{type:"boolean",default:!1},topic_tag:{type:"string",default:""},topic_tag_id:{type:"string",default:""},category_name:{type:"string",default:""},cat:{type:"string",default:""},categoryselector:{type:"boolean",default:!1},tag:{type:"string",default:""},tag_id:{type:"string",default:""},course_grid:{type:"boolean"},col:{type:"integer",default:ldlms_settings.plugins["learndash-course-grid"].enabled.col_default||3},example_show:{type:"boolean",default:0},preview_show:{type:"boolean",default:!0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{orderby:l,order:n,per_page:i,course_id:d,lesson_id:_,show_content:h,show_thumbnail:g,topic_category_name:m,topic_cat:b,topic_categoryselector:y,topic_tag:f,topic_tag_id:w,category_name:v,cat:x,categoryselector:k,tag:T,tag_id:P,course_grid:D,col:I,preview_show:S,example_show:z},setAttributes:q}=e;let B="",L="",O="",N=!0;if(!0===ldlms_settings.plugins["learndash-course-grid"].enabled){void 0===D||1!=D&&0!=D||(N=D);let e=!1;1==N&&(e=!0),O=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Grid Settings","learndash"),initialOpen:e},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Grid","learndash"),checked:!!N,onChange:e=>q({course_grid:e})}),(0,t.createElement)(s.RangeControl,{label:(0,a.__)("Columns","learndash"),value:I||ldlms_settings.plugins["learndash-course-grid"].enabled.col_default,min:1,max:ldlms_settings.plugins["learndash-course-grid"].enabled.col_max,step:1,onChange:e=>q({col:e})}))}B=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Content","learndash"),checked:!!h,onChange:e=>q({show_content:e})}),L=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Thumbnail","learndash"),checked:!!g,onChange:e=>q({show_thumbnail:e})});const U=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID to limit listing. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:d||"",type:"number",onChange:function(e){q(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s ID","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholders: Lesson, Course.
// translators: placeholders: Lesson, Course.
(0,a._x)("Enter single %1$s ID to limit listing. Leave blank if used within a %2$s.","placeholders: Lesson, Course","learndash"),u("lesson"),u("course")),value:_||"",type:"number",onChange:function(e){q(""!=e&&e<0?{lesson_id:"0"}:{lesson_id:e})}}),(0,t.createElement)(s.SelectControl,{key:"orderby",label:(0,a.__)("Order by","learndash"),value:l,options:[{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>q({orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"order",label:(0,a.__)("Order","learndash"),value:n,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>q({order:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("%s per page","placeholder: Topics","learndash"),u("topics")),help:(0,a.sprintf)(
// translators: placeholder: per_page.
// translators: placeholder: per_page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: per_page","learndash"),p("per_page")),value:i||"",type:"number",onChange:function(e){q(""!=e&&e<0?{per_page:"0"}:{per_page:e})}}),B,L);let A="";if("yes"===ldlms_settings.settings.topics_taxonomies.ld_topic_category){let e=!1;""==m&&""==b||(e=!0),A=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Category Settings","placeholder: Topic","learndash"),u("topic")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Category Slug","placeholder: Topic","learndash"),u("topic")),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned category slug.","placeholder: Topics","learndash"),u("topics")),value:m||"",onChange:e=>q({topic_category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Category ID","placeholder: Topic","learndash"),u("topic")),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Topics","learndash"),u("topics")),value:b||"",type:"number",onChange:function(e){q(""!=e&&e<0?{topic_cat:"0"}:{topic_cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: topic.
// translators: placeholder: topic.
(0,a._x)("%s Category Selector","placeholder: topic","learndash"),u("topic")),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows a %s category dropdown.","placeholder: Topics","learndash"),u("topics")),checked:!!y,onChange:e=>q({topic_categoryselector:e})}))}let $="";if("yes"===ldlms_settings.settings.topics_taxonomies.ld_topic_tag){let e=!1;""==f&&""==w||(e=!0),$=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Tag Settings","placeholder: Topic","learndash"),u("topic")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Tag Slug","placeholder: Topic","learndash"),u("topic")),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned tag slug.","placeholder: Topics","learndash"),u("topics")),value:f||"",onChange:e=>q({topic_tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Tag ID","placeholder: Topic","learndash"),u("topic")),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned tag ID.","placeholder: Topics","learndash"),u("topics")),value:w||"",type:"number",onChange:function(e){q(""!=e&&e<0?{topic_tag_id:"0"}:{topic_tag_id:e})}}))}let G="";if("yes"===ldlms_settings.settings.topics_taxonomies.wp_post_category){let e=!1;""==v&&""==x||(e=!0),G=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Category Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Category Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned WP Category slug.","placeholder: Topics","learndash"),u("topics")),value:v||"",onChange:e=>q({category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Topic.
// translators: placeholder: Topic.
(0,a._x)("%s Category ID","placeholder: Topic","learndash"),u("topic")),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Topics","learndash"),u("topics")),value:x||"",type:"number",onChange:function(e){q(""!=e&&e<0?{cat:"0"}:{cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("WP Category Selector","learndash"),help:(0,a.__)("shows a WP category dropdown.","learndash"),checked:!!k,onChange:e=>q({categoryselector:e})}))}let R="";if("yes"===ldlms_settings.settings.topics_taxonomies.wp_post_tag){let e=!1;""==T&&""==P||(e=!0),R=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Tag Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned WP tag slug.","placeholder: Topics","learndash"),u("topics")),value:T||"",onChange:e=>q({tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Topics.
// translators: placeholder: Topics.
(0,a._x)("shows %s with mentioned WP tag ID.","placeholder: Topics","learndash"),u("topics")),value:P||"",type:"number",onChange:function(e){q(""!=e&&e<0?{tag_id:"0"}:{tag_id:e})}}))}const Q=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!S,onChange:e=>q({preview_show:e})}));function M(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),E)}function W(e){return M()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},U,O,A,$,G,R,Q),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:C,attributes:a,key:C,EmptyResponsePlaceholder:W})):M();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const x="learndash/ld-quiz-list",k=(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("LearnDash %s List","placeholder: Quiz","learndash"),u("quiz"));(0,l.registerBlockType)(x,{title:k,description:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("This block shows a list of %s.","placeholder: Quizzes","learndash"),u("quizzes")),icon:"list-view",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{orderby:{type:"string",default:"ID"},order:{type:"string",default:"DESC"},per_page:{type:"string",default:""},course_id:{type:"string",default:""},lesson_id:{type:"string",default:""},show_content:{type:"boolean",default:!0},show_thumbnail:{type:"boolean",default:!0},quiz_category_name:{type:"string",default:""},quiz_cat:{type:"string",default:""},quiz_categoryselector:{type:"boolean",default:!1},quiz_tag:{type:"string",default:""},quiz_tag_id:{type:"string",default:""},category_name:{type:"string",default:""},cat:{type:"string",default:""},categoryselector:{type:"boolean",default:!1},tag:{type:"string",default:""},tag_id:{type:"string",default:""},course_grid:{type:"boolean"},col:{type:"integer",default:ldlms_settings.plugins["learndash-course-grid"].enabled.col_default||3},preview_show:{type:"boolean",default:!0},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{orderby:l,order:n,per_page:i,course_id:d,lesson_id:_,show_content:h,show_thumbnail:g,quiz_category_name:m,quiz_cat:b,quiz_categoryselector:y,quiz_tag:f,quiz_tag_id:w,category_name:v,cat:C,categoryselector:E,tag:T,tag_id:P,course_grid:D,col:I,preview_show:S,example_show:z},setAttributes:q}=e;let B="",L="",O="",N=!0;if(!0===ldlms_settings.plugins["learndash-course-grid"].enabled){void 0===D||1!=D&&0!=D||(N=D);let e=!1;1==N&&(e=!0),O=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Grid Settings","learndash"),initialOpen:e},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Grid","learndash"),checked:!!N,onChange:e=>q({course_grid:e})}),(0,t.createElement)(s.RangeControl,{label:(0,a.__)("Columns","learndash"),value:I||ldlms_settings.plugins["learndash-course-grid"].enabled.col_default,min:1,max:ldlms_settings.plugins["learndash-course-grid"].enabled.col_max,step:1,onChange:e=>q({col:e})}))}B=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Content","learndash"),checked:!!h,onChange:e=>q({show_content:e})}),L=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Thumbnail","learndash"),checked:!!g,onChange:e=>q({show_thumbnail:e})});const U=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID to limit listing. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:d||"",type:"number",onChange:function(e){q(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Lesson.
// translators: placeholder: Lesson.
(0,a._x)("%s ID","placeholder: Lesson","learndash"),u("lesson")),help:(0,a.sprintf)(
// translators: placeholders: Lesson, Course.
// translators: placeholders: Lesson, Course.
(0,a._x)("Enter single %1$s ID to limit listing. Leave blank if used within a %2$s. Zero for global.","placeholders: Lesson, Course","learndash"),u("lesson"),u("course")),value:_||"",type:"number",onChange:function(e){q(""!=e&&e<0?{lesson_id:"0"}:{lesson_id:e})}}),(0,t.createElement)(s.SelectControl,{key:"orderby",label:(0,a.__)("Order by","learndash"),value:l,options:[{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>q({orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"order",label:(0,a.__)("Order","learndash"),value:n,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>q({order:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("%s per page","placeholder: Quizzes","learndash"),u("quizzes")),help:(0,a.sprintf)(
// translators: placeholder: per_page.
// translators: placeholder: per_page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: per_page","learndash"),p("per_page")),value:i||"",type:"number",onChange:function(e){q(""!=e&&e<0?{per_page:"0"}:{per_page:e})}}),B,L);let A="";if("yes"===ldlms_settings.settings.quizzes_taxonomies.ld_quiz_category){let e=!1;""==m&&""==b||(e=!0),A=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Category Settings","placeholder: Quiz","learndash"),u("quiz")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Category Slug","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned category slug.","placeholder: Quizzes","learndash"),u("quizzes")),value:m||"",onChange:e=>q({quiz_category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Category ID","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Quizzes","learndash"),u("quizzes")),value:b||"",type:"number",onChange:function(e){q(""!=e&&e<0?{quiz_cat:"0"}:{quiz_cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Category Selector","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows a %s category dropdown.","placeholder: Quizzes","learndash"),u("quizzes")),checked:!!y,onChange:e=>q({quiz_categoryselector:e})}))}let $="";if("yes"===ldlms_settings.settings.quizzes_taxonomies.ld_quiz_tag){let e=!1;""==f&&""==w||(e=!0),$=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Tag Settings","placeholder: Quiz","learndash"),u("quiz")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Tag Slug","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned tag slug.","placeholder: Quizzes","learndash"),u("quizzes")),value:f||"",onChange:e=>q({quiz_tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Tag ID","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned tag ID.","placeholder: Quizzes","learndash"),u("quizzes")),value:w||"",type:"number",onChange:function(e){q(""!=e&&e<0?{quiz_tag_id:"0"}:{quiz_tag_id:e})}}))}let G="";if("yes"===ldlms_settings.settings.quizzes_taxonomies.wp_post_category){let e=!1;""==v&&""==C||(e=!0),G=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Category Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Category Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned WP Category slug.","placeholder: Quizzes","learndash"),u("quizzes")),value:v||"",onChange:e=>q({category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Category ID","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Quizzes","learndash"),u("quizzes")),value:C||"",type:"number",onChange:function(e){q(""!=e&&e<0?{cat:"0"}:{cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("WP Category Selector","learndash"),help:(0,a.__)("shows a WP category dropdown.","learndash"),checked:!!E,onChange:e=>q({categoryselector:e})}))}let R="";if("yes"===ldlms_settings.settings.quizzes_taxonomies.wp_post_tag){let e=!1;""==T&&""==P||(e=!0),R=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Tag Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned WP tag slug.","placeholder: Quizzes","learndash"),u("quizzes")),value:T||"",onChange:e=>q({tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("shows %s with mentioned WP tag ID.","placeholder: Quizzes","learndash"),u("quizzes")),value:P||"",type:"number",onChange:function(e){q(""!=e&&e<0?{tag_id:"0"}:{tag_id:e})}}))}const Q=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!S,onChange:e=>q({preview_show:e})}));function M(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),k)}function W(e){return M()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},U,O,A,$,G,R,Q),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:x,attributes:a,key:x,EmptyResponsePlaceholder:W})):M();var a}),[e.attributes])]},save:e=>{}});const T=wp.element.createElement,P=T("svg",{width:300,height:300,viewBox:"0 0 50 10"},T("path",{d:"M47.1,0h-44c-1.7,0-3,1.3-3,3v4c0,1.7,1.3,3,3,3h44c1.7,0,3-1.3,3-3V3C50.1,1.3,48.7,0,47.1,0z M48.1,7c0,0.6-0.4,1-1,1h-12 V2h12c0.6,0,1,0.4,1,1V7z"})),D="learndash/ld-course-progress",I=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Progress","placeholders: Course","learndash"),u("course"));(0,l.registerBlockType)(D,{title:I,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block displays users progress bar for the %s.","placeholders: Course","learndash"),u("course")),icon:P,category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},user_id:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string"},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:e=>{let{attributes:{course_id:l},className:n}=e;const{attributes:{user_id:i,preview_show:d,preview_user_id:p,example_show:_},setAttributes:h}=e,g=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){h(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:i||"",type:"number",onChange:function(e){h(""!=e&&e<0?{user_id:"0"}:{user_id:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!d,onChange:e=>h({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:p||"",type:"number",onChange:function(e){h(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function m(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),I)}function b(e){return m()}return[g,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:D,attributes:a,key:D,EmptyResponsePlaceholder:b})):m();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const S=(0,a.__)("LearnDash Visitor","learndash");(0,l.registerBlockType)("learndash/ld-visitor",{title:S,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block shows the content if the user is not enrolled into the %s.","placeholder: Course","learndash"),u("course")),icon:"visibility",supports:{customClassName:!1},category:"learndash-blocks",attributes:{display_type:{type:"string",default:""},course_id:{type:"string",default:""},group_id:{type:"string",default:""},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{display_type:l,course_id:n,group_id:o,user_id:i,autop:d},className:p,setAttributes:h}=e;var g,m;g=(0,t.createElement)(s.SelectControl,{key:"display_type",label:(0,a.__)("Display Type","learndash"),value:l,options:[{label:(0,a.__)("Select a Display Type","learndash"),value:""},{label:u("course"),value:"sfwd-courses"},{label:u("group"),value:"groups"}],help:(0,a.sprintf)(
// translators: placeholders: Course, Group.
// translators: placeholders: Course, Group.
(0,a._x)("Leave blank to show the default %1$s or %2$s content table.","placeholders: Course, Group","learndash"),u("course"),u("group")),onChange:e=>h({display_type:e})}),"sfwd-courses"===l?(h({group_id:""}),m=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:n||"",type:"number",onChange:function(e){h(""!=e&&e<0?{course_id:"0"}:{course_id:e})}})):"groups"===l&&(h({course_id:""}),m=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group")),value:o||"",type:"number",onChange:function(e){h(""!=e&&e<0?{group_id:"0"}:{group_id:e})}}));const b=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},g,m,(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:i||"",type:"number",onChange:function(e){h(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!d,onChange:e=>h({autop:e})})));let y=l;if(""===y){let e=c();void 0!==e.post_type&&("sfwd-courses"===e.post_type?y="sfwd-courses":"groups"===e.post_type&&(y="groups"))}let f="";if("sfwd-courses"===y){let e=_(n);0===e&&(e=c("course_id"),e=_(e),0==e&&(f=(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course"))))}else if("groups"===y){let e=_(o);0===e&&(e=c("post_id"),e=_(e),0==e&&(f=(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group"))))}return f.length&&(f=(0,t.createElement)("span",{className:"learndash-block-error-message"},f)),[b,(0,t.createElement)("div",{className:p,key:"learndash/ld-visitor"},(0,t.createElement)("span",{className:"learndash-inner-header"},S),(0,t.createElement)("div",{className:"learndash-block-inner"},f,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const z=(0,a.__)("LearnDash Student","learndash");(0,l.registerBlockType)("learndash/ld-student",{title:z,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block shows the content if the user is enrolled in the %s.","placeholders: Course","learndash"),u("course")),icon:"welcome-learn-more",category:"learndash-blocks",supports:{customClassName:!1},attributes:{display_type:{type:"string",default:""},course_id:{type:"string",default:""},group_id:{type:"string",default:""},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{display_type:l,course_id:n,group_id:o,user_id:i,autop:d},className:p,setAttributes:h}=e;var g,m;g=(0,t.createElement)(s.SelectControl,{key:"display_type",label:(0,a.__)("Display Type","learndash"),value:l,options:[{label:(0,a.__)("Select a Display Type","learndash"),value:""},{label:u("course"),value:"sfwd-courses"},{label:u("group"),value:"groups"}],help:(0,a.sprintf)(
// translators: placeholders: Course, Group.
// translators: placeholders: Course, Group.
(0,a._x)("Leave blank to show the default %1$s or %2$s content table.","placeholders: Course, Group","learndash"),u("course"),u("group")),onChange:e=>h({display_type:e})}),"sfwd-courses"===l?(h({group_id:""}),m=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:n||"",type:"number",onChange:function(e){h(""!=e&&e<0?{course_id:"0"}:{course_id:e})}})):"groups"===l&&(h({course_id:""}),m=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group")),value:o||"",type:"number",onChange:function(e){h(""!=e&&e<0?{group_id:"0"}:{group_id:e})}}));const b=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},g,m,(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:i||"",type:"number",onChange:function(e){h(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!d,onChange:e=>h({autop:e})})));let y=l;if(""===y){let e=c();void 0!==e.post_type&&("sfwd-courses"===e.post_type?y="sfwd-courses":"groups"===e.post_type&&(y="groups"))}let f="";if("sfwd-courses"===y){let e=_(n);0===e&&(e=c("course_id"),e=_(e),0==e&&(f=(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course"))))}else if("groups"===y){let e=_(o);0===e&&(e=c("post_id"),e=_(e),0==e&&(f=(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group"))))}return f.length&&(f=(0,t.createElement)("span",{className:"learndash-block-error-message"},f)),[b,(0,t.createElement)("div",{className:p,key:"learndash/ld-student"},(0,t.createElement)("span",{className:"learndash-inner-header"},z),(0,t.createElement)("div",{className:"learndash-block-inner"},f,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const q=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Complete","placeholder: Course","learndash"),u("course")),B="learndash/ld-course-complete";(0,l.registerBlockType)(B,{title:q,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block shows the content if the user is enrolled into the %s and it is completed.","placeholders: Course","learndash"),u("course")),icon:"star-filled",category:"learndash-blocks",supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{course_id:l,user_id:n,autop:o},className:i,setAttributes:d}=e,p=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:n||"",type:"number",onChange:function(e){d(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!o,onChange:e=>d({autop:e})})));let h="",g=_(l);return 0===g&&(g=_(c("course_id"))),0==g&&(h=(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course"))),h.length&&(h=(0,t.createElement)("span",{className:"learndash-block-error-message"},h)),[p,(0,t.createElement)("div",{className:i,key:B},(0,t.createElement)("span",{className:"learndash-inner-header"},q),(0,t.createElement)("div",{className:"learndash-block-inner"},h,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const L=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s In Progress","placeholder: Course","learndash"),u("course")),O="learndash/ld-course-inprogress";(0,l.registerBlockType)(O,{title:L,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block shows the content if the user is enrolled into the %s but not yet completed.","placeholder: Course","learndash"),u("course")),icon:"star-half",category:"learndash-blocks",supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{course_id:l,user_id:n,autop:o},className:i,setAttributes:d}=e,p=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:n||"",type:"number",onChange:function(e){d(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!o,onChange:e=>d({autop:e})})));let h="",g=_(l);return 0===g&&(g=c("course_id"),g=_(g),0==g&&(h=(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")))),h.length&&(h=(0,t.createElement)("span",{className:"learndash-block-error-message"},h)),[p,(0,t.createElement)("div",{className:i,key:O},(0,t.createElement)("span",{className:"learndash-inner-header"},L),(0,t.createElement)("div",{className:"learndash-block-inner"},h,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const N=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Not Started","placeholder: Course","learndash"),u("course")),U="learndash/ld-course-notstarted";(0,l.registerBlockType)(U,{title:N,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block shows the content if the user is enrolled into the %s but not yet started.","placeholder: Course","learndash"),u("course")),icon:"star-empty",category:"learndash-blocks",supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{course_id:l,user_id:n,autop:o},className:i,setAttributes:d}=e,p=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:n||"",type:"number",onChange:function(e){d(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!o,onChange:e=>d({autop:e})})));let h="",g=_(l);return 0===g&&(g=c("course_id"),g=_(g),0==g&&(h=(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")))),h.length&&(h=(0,t.createElement)("span",{className:"learndash-block-error-message"},h)),[p,(0,t.createElement)("div",{className:i,key:U},(0,t.createElement)("span",{className:"learndash-inner-header"},N),(0,t.createElement)("div",{className:"learndash-block-inner"},h,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const A="learndash/ld-course-resume",$=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Resume","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(A,{title:$,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Return to %s link/button.","placeholder: Course","learndash"),u("course")),icon:"welcome-learn-more",category:"learndash-blocks",supports:{customClassName:!1},example:{attributes:{example_show:1}},attributes:{course_id:{type:"string",default:""},user_id:{type:"string",default:""},label:{type:"string",default:""},html_class:{type:"string",default:""},button:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{course_id:l,user_id:n,label:i,html_class:d,button:p,preview_show:_,preview_user_id:h,example_show:g},className:m,setAttributes:b}=e,y=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){b(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:n||"",type:"number",onChange:function(e){b(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.SelectControl,{key:"button",label:(0,a.__)("Show as button","learndash"),value:p,options:[{label:(0,a.__)("Yes","learndash"),value:"true"},{label:(0,a.__)("No","learndash"),value:"false"}],onChange:e=>b({button:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Label","learndash"),help:(0,a.__)("Label for link shown to user","learndash"),value:i||"",onChange:e=>b({label:e})}),(0,t.createElement)(s.TextControl,{key:"html_class",label:(0,a.__)("Class","learndash"),help:(0,a.__)("HTML class for link element","learndash"),value:d||"",onChange:e=>b({html_class:e})})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!_,onChange:e=>b({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:h||"",type:"number",onChange:function(e){b(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function f(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),$)}function w(e){return f()}return[y,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:A,attributes:a,key:A,EmptyResponsePlaceholder:w})):f();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const G="learndash/ld-course-info",R=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Info [ld_course_info]","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(G,{title:R,description:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("This block shows the %s and progress for the user.","placeholder: Courses","learndash"),u("course")),icon:"analytics",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{user_id:{type:"string",default:0},registered_show:{type:"boolean"},registered_show_thumbnail:{type:"boolean",default:!0},registered_num:{type:"string",default:""},registered_orderby:{type:"string",default:"title"},registered_order:{type:"string",default:"ASC"},progress_show:{type:"boolean"},progress_num:{type:"string",default:""},progress_orderby:{type:"string",default:"title"},progress_order:{type:"string",default:"ASC"},quiz_show:{type:"boolean"},quiz_num:{type:"string",default:""},quiz_orderby:{type:"string",default:"taken"},quiz_order:{type:"string",default:"DESC"},preview_show:{type:"boolean",default:!0},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{user_id:l,registered_show:n,registered_show_thumbnail:i,registered_num:d,registered_orderby:_,registered_order:h,progress_show:g,progress_num:m,progress_orderby:b,progress_order:y,quiz_show:f,quiz_num:w,quiz_orderby:v,quiz_order:C,preview_user_id:E,preview_show:x},setAttributes:k}=e;void 0===n&&k({registered_show:!0}),void 0===g&&k({progress_show:!0}),void 0===f&&k({quiz_show:!0}),!1===n&&!1===g&&!1===f&&(k({registered_show:!0}),k({progress_show:!0}),k({quiz_show:!0}));const T=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:l||"",type:"number",onChange:function(e){k(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("Show Registered %s","placeholder: Courses","learndash"),u("courses")),checked:!!n,onChange:e=>k({registered_show:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Show %s Progress","placeholder: Course","learndash"),u("course")),checked:!!g,onChange:e=>k({progress_show:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("Show %s Attempts","placeholder: Quiz","learndash"),u("quiz")),checked:!!f,onChange:e=>k({quiz_show:e})}));var P="";!0===n&&(P=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Courses.
// translators: placeholder: Courses.
(0,a._x)("Registered %s","placeholder: Courses","learndash"),u("courses")),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Thumbnail","learndash"),checked:!!i,onChange:e=>k({registered_show_thumbnail:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("per page","learndash"),help:(0,a.sprintf)(
// translators: placeholder: per_page.
// translators: placeholder: per_page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: per_page","learndash"),p("per_page")),value:d||"",min:0,max:100,type:"number",onChange:function(e){k(""!=e&&e<0?{registered_num:"0"}:{registered_num:e})}}),(0,t.createElement)(s.SelectControl,{key:"registered_orderby",label:(0,a.__)("Order by","learndash"),value:_,options:[{label:(0,a.__)("Title - Order by post title (default)","learndash"),value:"title"},{label:(0,a.__)("ID - Order by post id","learndash"),value:"ID"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>k({registered_orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"registered_order",label:(0,a.__)("Order","learndash"),value:h,options:[{label:(0,a.__)("ASC - lowest to highest values (default)","learndash"),value:"ASC"},{label:(0,a.__)("DESC - highest to lowest values","learndash"),value:"DESC"}],onChange:e=>k({registered_order:e})})));var D="";!0===g&&(D=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Progress","placeholder: Course","learndash"),u("course")),initialOpen:!1},(0,t.createElement)(s.TextControl,{label:(0,a.__)("per page","learndash"),help:(0,a.sprintf)(
// translators: placeholder: progress_num.
// translators: placeholder: progress_num.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: progress_num","learndash"),p("progress_num")),value:m||"",min:0,max:100,type:"number",onChange:function(e){k(""!=e&&e<0?{progress_num:"0"}:{progress_num:e})}}),(0,t.createElement)(s.SelectControl,{key:"progress_orderby",label:(0,a.__)("Order by","learndash"),value:b,options:[{label:(0,a.__)("Title - Order by post title (default)","learndash"),value:"title"},{label:(0,a.__)("ID - Order by post id","learndash"),value:"ID"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>k({progress_orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"progress_order",label:(0,a.__)("Order","learndash"),value:y,options:[{label:(0,a.__)("ASC - lowest to highest values (default)","learndash"),value:"ASC"},{label:(0,a.__)("DESC - highest to lowest values","learndash"),value:"DESC"}],onChange:e=>k({progress_order:e})})));var I="";!0===f&&(I=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Attempts","placeholder: Quiz","learndash"),u("quiz")),initialOpen:!1},(0,t.createElement)(s.TextControl,{label:(0,a.__)("per page","learndash"),help:(0,a.sprintf)(
// translators: placeholder: quiz_num.
// translators: placeholder: quiz_num.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: quiz_num","learndash"),p("quiz_num")),value:w||"",min:0,max:100,type:"number",onChange:function(e){k(""!=e&&e<0?{quiz_num:"0"}:{quiz_num:e})}}),(0,t.createElement)(s.SelectControl,{key:"quiz_orderby",label:(0,a.__)("Order by","learndash"),value:v,options:[{label:(0,a.__)("Date Taken (default) - Order by date taken","learndash"),value:"taken"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>k({quiz_orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"quiz_order",label:(0,a.__)("Order","learndash"),value:C,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>k({quiz_order:e})})));const S=(0,t.createElement)(r.InspectorControls,{key:"controls"},T,P,D,I,(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!x,onChange:e=>k({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:E||"",type:"number",onChange:function(e){k(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function z(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),R)}function q(e){return z()}return[S,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:G,attributes:a,key:G,EmptyResponsePlaceholder:q})):z();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const Q="learndash/ld-user-course-points",M=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash User %s Points","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(Q,{title:M,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block shows the earned %s points for the user.","placeholder: Course","learndash"),u("course")),icon:"chart-area",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{user_id:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string"},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{user_id:l,preview_show:n,preview_user_id:i},setAttributes:d}=e,u=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{user_id:"0"}:{user_id:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!n,onChange:e=>d({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:i||"",type:"number",onChange:function(e){d(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function p(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),M)}function _(e){return p()}return[u,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:Q,attributes:a,key:Q,EmptyResponsePlaceholder:_})):p();var a}),[e.attributes])]},save:e=>{delete e.attributes.preview_user_id}});const W="learndash/ld-group-list",j=(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("LearnDash %s List","placeholder: Group","learndash"),u("group"));(0,l.registerBlockType)(W,{title:j,description:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("This block shows a list of %s.","placeholder: Groups","learndash"),u("groups")),icon:"list-view",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{orderby:{type:"string",default:"ID"},order:{type:"string",default:"DESC"},per_page:{type:"string",default:""},mygroups:{type:"string",default:""},status:{type:"array",default:["not_started","in_progress","completed"]},show_content:{type:"boolean",default:!0},show_thumbnail:{type:"boolean",default:!0},group_category_name:{type:"string",default:""},group_cat:{type:"string",default:""},group_categoryselector:{type:"boolean",default:!1},group_tag:{type:"string",default:""},group_tag_id:{type:"string",default:""},category_name:{type:"string",default:""},cat:{type:"string",default:""},categoryselector:{type:"boolean",default:!1},tag:{type:"string",default:""},tag_id:{type:"string",default:""},course_grid:{type:"boolean"},progress_bar:{type:"boolean",default:!1},col:{type:"integer",default:ldlms_settings.plugins["learndash-course-grid"].enabled.col_default||3},price_type:{type:"array",default:["free","paynow","subscribe","closed"]},preview_show:{type:"boolean",default:!0},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{orderby:l,order:n,per_page:i,mygroups:d,status:_,show_content:h,show_thumbnail:g,group_category_name:m,group_cat:b,group_categoryselector:y,group_tag:f,group_tag_id:w,category_name:v,cat:C,categoryselector:E,tag:x,tag_id:k,course_grid:T,progress_bar:P,col:D,preview_user_id:I,preview_show:S,example_show:z,price_type:q},setAttributes:B}=e;let L="",O="",N="",U=!0;if(!0===ldlms_settings.plugins["learndash-course-grid"].enabled){void 0===T||1!=T&&0!=T||(U=T);let e=!1;1==U&&(e=!0),N=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Grid Settings","learndash"),initialOpen:e},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Grid","learndash"),checked:!!U,onChange:e=>B({course_grid:e})}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Progress Bar","learndash"),checked:!!P,onChange:e=>B({progress_bar:e})}),(0,t.createElement)(s.RangeControl,{label:(0,a.__)("Columns","learndash"),value:D||ldlms_settings.plugins["learndash-course-grid"].enabled.col_default,min:1,max:ldlms_settings.plugins["learndash-course-grid"].enabled.col_max,step:1,onChange:e=>B({col:e})}))}L=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Content","learndash"),checked:!!h,onChange:e=>B({show_content:e})}),O=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Thumbnail","learndash"),checked:!!g,onChange:e=>B({show_thumbnail:e})});let A="";""===ldlms_settings.settings.groups_cpt.public&&(A=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Warning","learndash"),opened:!0},(0,t.createElement)(s.TextControl,{help:(0,a.sprintf)(
// translators: placeholders: Groups, Groups.
// translators: placeholders: Groups, Groups.
(0,a._x)("%1$s are not public, please visit the %2$s Settings page and set them to Public to enable access on the front end.","placeholders: Groups, Groups","learndash"),u("groups"),u("groups")),value:"",type:"hidden",className:"notice notice-error"})));const $=(0,t.createElement)(s.PanelBody,{className:"learndash-block-controls-panel learndash-block-controls-panel-ld-group-list",title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.SelectControl,{key:"orderby",label:(0,a.__)("Order by","learndash"),value:l,options:[{label:(0,a.__)("ID - Order by post id. (default)","learndash"),value:"ID"},{label:(0,a.__)("Title - Order by post title","learndash"),value:"title"},{label:(0,a.__)("Date - Order by post date","learndash"),value:"date"},{label:(0,a.__)("Menu - Order by Page Order Value","learndash"),value:"menu_order"}],onChange:e=>B({orderby:e})}),(0,t.createElement)(s.SelectControl,{key:"order",label:(0,a.__)("Order","learndash"),value:n,options:[{label:(0,a.__)("DESC - highest to lowest values (default)","learndash"),value:"DESC"},{label:(0,a.__)("ASC - lowest to highest values","learndash"),value:"ASC"}],onChange:e=>B({order:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("%s per page","placeholder: Groups","learndash"),u("groups")),help:(0,a.sprintf)(
// translators: placeholder: default per page.
// translators: placeholder: default per page.
(0,a._x)("Leave empty for default (%d) or 0 to show all items.","placeholder: default per page","learndash"),p("per_page")),value:i||"",type:"number",onChange:function(e){B(""!=e&&e<0?{per_page:"0"}:{per_page:e})}}),(0,t.createElement)(s.SelectControl,{multiple:!0,key:"price_type",label:(0,a.sprintf)(
// translators: placeholder: Group Access Mode(s).
// translators: placeholder: Group Access Mode(s).
(0,a._x)("%s Access Mode(s)","placeholder: Group Access Mode(s)","learndash"),u("group")),help:(0,a.__)("Ctrl+click to deselect selected items.","learndash"),value:q,options:[{label:(0,a.__)("Free","learndash"),value:"free"},{label:(0,a.__)("Buy Now","learndash"),value:"paynow"},{label:(0,a.__)("Recurring","learndash"),value:"subscribe"},{label:(0,a.__)("Closed","learndash"),value:"closed"}],onChange:e=>B({price_type:e})}),(0,t.createElement)(s.SelectControl,{key:"mygroups",label:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("My %s","placeholder: Groups","learndash"),u("groups")),value:d,options:[{label:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("Show All %s (default)","placeholder: Groups","learndash"),u("groups")),value:""},{label:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("Show Enrolled %s only","placeholder: Groups","learndash"),u("groups")),value:"enrolled"},{label:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("Show not-Enrolled %s only","placeholder: Groups","learndash"),u("Groups")),value:"not-enrolled"}],onChange:e=>B({mygroups:e})}),"enrolled"===d&&(0,t.createElement)(s.SelectControl,{multiple:!0,key:"status",label:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("Enrolled %s Status","placeholder: Groups","learndash"),u("groups")),value:_,options:[{label:(0,a.__)("Not Started","learndash"),value:"not_started"},{label:(0,a.__)("In Progress","learndash"),value:"in_progress"},{label:(0,a.__)("Completed","learndash"),value:"completed"}],onChange:e=>B({status:e})}),L,O);let G="";if("yes"===ldlms_settings.settings.groups_taxonomies.ld_group_category){let e=!1;""==m&&""==b||(e=!0),G=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Category Settings","placeholder: Group","learndash"),u("group")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Category Slug","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned category slug.","placeholder: Groups","learndash"),u("groups")),value:m||"",onChange:e=>B({group_category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Category ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Groups","learndash"),u("groups")),value:b||"",type:"number",onChange:function(e){B(""!=e&&e<0?{group_cat:"0"}:{group_cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Category Selector","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows a %s category dropdown.","placeholder: Groups","learndash"),u("groups")),checked:!!y,onChange:e=>B({group_categoryselector:e})}))}let R="";if("yes"===ldlms_settings.settings.groups_taxonomies.ld_group_tag){let e=!1;""==f&&""==w||(e=!0),R=(0,t.createElement)(s.PanelBody,{title:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Tag Settings","placeholder: Group","learndash"),u("group")),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Tag Slug","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned tag slug.","placeholder: Groups","learndash"),u("groups")),value:f||"",onChange:e=>B({group_tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Tag ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned tag ID.","placeholder: Groups","learndash"),u("groups")),value:w||"",type:"number",onChange:function(e){B(""!=e&&e<0?{group_tag_id:"0"}:{group_tag_id:e})}}))}let Q="";if("yes"===ldlms_settings.settings.groups_taxonomies.wp_post_category){let e=!1;""==v&&""==C||(e=!0),Q=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Category Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Category Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned WP Category slug.","placeholder: Groups","learndash"),u("groups")),value:v||"",onChange:e=>B({category_name:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Category ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned category ID.","placeholder: Groups","learndash"),u("groups")),value:C||"",type:"number",onChange:function(e){B(""!=e&&e<0?{cat:"0"}:{cat:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("WP Category Selector","learndash"),help:(0,a.__)("shows a WP category dropdown.","learndash"),checked:!!E,onChange:e=>B({categoryselector:e})}))}let M="";if("yes"===ldlms_settings.settings.groups_taxonomies.wp_post_tag){let e=!1;""==x&&""==k||(e=!0),M=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("WP Tag Settings","learndash"),initialOpen:e},(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag Slug","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned WP tag slug.","placeholder: Groups","learndash"),u("groups")),value:x||"",onChange:e=>B({tag:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("WP Tag ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("shows %s with mentioned WP tag ID.","placeholder: Groups","learndash"),u("groups")),value:k||"",type:"number",onChange:function(e){B(""!=e&&e<0?{tag_id:"0"}:{tag_id:e})}}))}const F=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!S,onChange:e=>B({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:I||"",type:"number",onChange:function(e){B(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function V(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),j)}function H(e){return V()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},A,$,N,G,R,Q,M,F),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:W,attributes:a,key:W,EmptyResponsePlaceholder:H})):V();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const F="learndash/ld-user-groups",V=(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("LearnDash User %s","placeholder: Groups","learndash"),u("groups"));(0,l.registerBlockType)(F,{title:V,description:(0,a.sprintf)(
// translators: placeholder: Groups.
// translators: placeholder: Groups.
(0,a._x)("This block displays the list of %s users are assigned to as users or leaders.","placeholder: Groups","learndash"),u("groups")),icon:"groups",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{user_id:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string"},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{user_id:l,preview_user_id:n,preview_show:i},setAttributes:d}=e;let p="";""===ldlms_settings.settings.groups_cpt.public&&(p=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Warning","learndash"),opened:!0},(0,t.createElement)(s.TextControl,{help:(0,a.sprintf)(
// translators: placeholders: Groups, Groups.
// translators: placeholders: Groups, Groups.
(0,a._x)("%1$s are not public, please visit the %2$s Settings page and set them to Public to enable access on the front end.","placeholders: Groups, Groups","learndash"),u("groups"),u("groups")),value:"",type:"hidden",className:"notice notice-error"})));const _=(0,t.createElement)(r.InspectorControls,{key:"controls"},p,(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{user_id:"0"}:{user_id:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!i,onChange:e=>d({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:n||"",type:"number",onChange:function(e){d(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function h(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),V)}function g(e){return h()}return[_,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:F,attributes:a,key:F,EmptyResponsePlaceholder:g})):h();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const H=(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("LearnDash %s","placeholder: Group","learndash"),u("group"));(0,l.registerBlockType)("learndash/ld-group",{title:H,description:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("This block shows the content if the user is enrolled into the %s.","placeholder: Group","learndash"),u("group")),icon:"groups",category:"learndash-blocks",supports:{customClassName:!1},attributes:{group_id:{type:"string"},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{group_id:l,user_id:n,autop:o},className:i,setAttributes:d}=e,c=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID (required)","placeholder: Group","learndash"),u("group")),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{group_id:"0"}:{group_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:n||"",type:"number",onChange:function(e){d(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!o,onChange:e=>d({autop:e})})));let p="";return 0==_(l)&&(p=(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID is required.","placeholder: Group","learndash"),u("group"))),p.length&&(p=(0,t.createElement)("span",{className:"learndash-block-error-message"},p)),[c,(0,t.createElement)("div",{className:i,key:"learndash/ld-group"},(0,t.createElement)("span",{className:"learndash-inner-header"},H),(0,t.createElement)("div",{className:"learndash-block-inner"},p,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const Z="learndash/ld-payment-buttons",Y=(0,a.__)("LearnDash Payment Buttons","learndash");(0,l.registerBlockType)(Z,{title:Y,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block displays the %s payment buttons","placeholder: Course","learndash"),u("course")),icon:"cart",category:"learndash-blocks",supports:{customClassName:!1},attributes:{display_type:{type:"string",default:""},course_id:{type:"string"},group_id:{type:"string"},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{display_type:l,course_id:n,group_id:i,preview_show:d,preview_user_id:p},className:_,setAttributes:h}=e;var g,m;g=(0,t.createElement)(s.SelectControl,{key:"display_type",label:(0,a.__)("Display Type","learndash"),value:l,options:[{label:(0,a.__)("Select a Display Type","learndash"),value:""},{label:u("course"),value:"sfwd-courses"},{label:u("group"),value:"groups"}],help:(0,a.sprintf)(
// translators: placeholders: Course, Group.
// translators: placeholders: Course, Group.
(0,a._x)("Leave blank to show the default %1$s or %2$s content table.","placeholders: Course, Group","learndash"),u("course"),u("group")),onChange:e=>h({display_type:e})}),"sfwd-courses"===l?(h({group_id:""}),m=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:n||"",type:"number",onChange:function(e){h(""!=e&&e<0?{course_id:"0"}:{course_id:e})}})):"groups"===l&&(h({course_id:""}),m=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group")),value:i||"",type:"number",onChange:function(e){h(""!=e&&e<0?{group_id:"0"}:{group_id:e})}}));const b=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},g,m),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!d,onChange:e=>h({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID for preview.","learndash"),value:p||"",type:"number",onChange:function(e){h(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function y(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),Y)}function f(e){return y()}return[b,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:Z,attributes:a,key:Z,EmptyResponsePlaceholder:f})):y();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const J="learndash/ld-course-content",K=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Content","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(J,{title:K,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block displays the %s Content table.","placeholder: Course","learndash"),u("course")),icon:"format-aside",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{display_type:{type:"string",default:""},course_id:{type:"string",default:""},post_id:{type:"string",default:""},group_id:{type:"string",default:""},per_page:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{display_type:l,course_id:n,post_id:i,group_id:d,per_page:p,preview_show:_,preview_user_id:h,example_show:m},className:b,setAttributes:y}=e;var f,w;f=(0,t.createElement)(s.SelectControl,{key:"display_type",label:(0,a.__)("Display Type","learndash"),value:l,options:[{label:(0,a.__)("Select a Display Type","learndash"),value:""},{label:u("course"),value:"sfwd-courses"},{label:u("group"),value:"groups"}],help:(0,a.sprintf)(
// translators: placeholders: Course, Group.
// translators: placeholders: Course, Group.
(0,a._x)("Leave blank to show the default %1$s or %2$s content table.","placeholders: Course, Group","learndash"),u("course"),u("group")),onChange:e=>y({display_type:e})}),"sfwd-courses"===l?(y({group_id:""}),w=(0,t.createElement)(React.Fragment,null,(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:n||"",type:"number",onChange:function(e){y(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Step ID","learndash"),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single Step ID. Leave blank if used within a %1$s or 0 to always show %2$s content table.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:i||"",type:"number",onChange:function(e){y(""!=e&&e<0?{post_id:"0"}:{post_id:e})}}))):"groups"===l&&(y({course_id:""}),y({post_id:""}),w=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group")),value:d||"",type:"number",onChange:function(e){y(""!=e&&e<0?{group_id:"0"}:{group_id:e})}}));const v=(0,t.createElement)(r.InspectorControls,{key:"controls"},g(),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},f,w,(0,t.createElement)(s.TextControl,{label:(0,a.__)("Items per page","learndash"),help:(0,a.__)("Leave empty for default or 0 to show all items.","learndash"),value:p||"",type:"number",onChange:function(e){y(""!=e&&e<0?{per_page:"0"}:{per_page:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!_,onChange:e=>y({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID for preview.","learndash"),value:h||"",type:"number",onChange:function(e){y(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function C(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),K)}function E(e){return C()}return[v,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:J,attributes:a,key:J,EmptyResponsePlaceholder:E})):C();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const X="learndash/ld-course-expire-status",ee=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Expire Status","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(X,{title:ee,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block displays the user %s access expire date.","placeholders: Course","learndash"),u("course")),icon:"clock",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},user_id:{type:"string",default:""},label_before:{type:"string",default:""},label_after:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:function(e){let{attributes:{course_id:l},className:n}=e;const{attributes:{user_id:i,label_before:d,label_after:p,preview_show:_,preview_user_id:h,example_show:g},setAttributes:m}=e,b=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){m(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:i||"",type:"number",onChange:function(e){m(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Label Before Expire","learndash"),help:(0,a.__)("The label prefix shown before the access expires","learndash"),value:d||"",onChange:e=>m({label_before:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Label After Expire","learndash"),help:(0,a.__)("The label prefix shown after access has expired","learndash"),value:p||"",onChange:e=>m({label_after:e})})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!_,onChange:e=>m({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:h||"",type:"number",onChange:function(e){m(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function y(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),ee)}function f(e){return y()}return[b,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:X,attributes:a,key:X,EmptyResponsePlaceholder:f})):y();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const te="learndash/ld-certificate",ae=(0,a.__)("LearnDash Certificate","learndash");(0,l.registerBlockType)(te,{title:ae,description:(0,a.__)("This shortcode shows a Certificate download link.","learndash"),icon:"welcome-learn-more",category:"learndash-blocks",supports:{customClassName:!1},example:{attributes:{example_show:1}},attributes:{display_type:{type:"string",default:""},course_id:{type:"string",default:""},group_id:{type:"string",default:""},quiz_id:{type:"string",default:""},user_id:{type:"string",default:""},display_as:{type:"string",default:""},label:{type:"string",default:""},class_html:{type:"string",default:""},context:{type:"string",default:""},callback:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{display_type:l,course_id:n,group_id:i,quiz_id:d,user_id:p,display_as:_,label:h,class_html:m,context:b,callback:y,preview_show:f,preview_user_id:w,example_show:v},title:C,className:E,setAttributes:x}=e;var k,T,P;""==_&&("sfwd-courses"!=c("post_type")&&"groups"!=c("post_type")||x({display_as:"banner"})),k=(0,t.createElement)(s.SelectControl,{key:"display_type",label:(0,a.__)("Display Type","learndash"),value:l,help:(0,a.sprintf)(
// translators: placeholders: Course, Group, Quiz.
// translators: placeholders: Course, Group, Quiz.
(0,a._x)("Require if not used within a %1$s, %2$s, or %3$s. Or to override default display.","placeholders: Course, Group, Quiz","learndash"),u("course"),u("group"),u("quiz")),options:[{label:(0,a.__)("Select a Display Type","learndash"),value:""},{label:u("course"),value:"sfwd-courses"},{label:u("group"),value:"groups"},{label:u("quiz"),value:"sfwd-quiz"}],onChange:e=>x({display_type:e})}),"sfwd-courses"===l?(x({group_id:""}),x({quiz_id:""}),T=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Enter single %s ID.","placeholder: Course","learndash"),u("course")),value:n||"",type:"number",onChange:function(e){x(""!=e&&e<0?{course_id:"0"}:{course_id:e})}})):"groups"===l?(x({course_id:""}),x({quiz_id:""}),T=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("Enter single %s ID.","placeholder: Group","learndash"),u("group")),value:i||"",type:"number",onChange:function(e){x(""!=e&&e<0?{group_id:"0"}:{group_id:e})}})):"sfwd-quiz"===l&&(x({group_id:""}),T=(0,t.createElement)(React.Fragment,null,(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s ID","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("Enter single %s ID.","placeholder: Quiz","learndash"),u("quiz")),value:d||"",type:"number",onChange:function(e){x(""!=e&&e<0?{quiz_id:"0"}:{quiz_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Quiz, Course.
// translators: placeholders: Course, Quiz, Course.
(0,a._x)("Enter single %1$s ID. Required if %2$s is within a %3$s","placeholders: Course, Quiz, Course","learndash"),u("course"),u("quiz"),u("course")),value:n||"",type:"number",onChange:function(e){x(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}))),"button"==_&&(P=(0,t.createElement)(React.Fragment,null,(0,t.createElement)(s.TextControl,{label:(0,a.__)("Label","learndash"),help:(0,a.__)("Label for link shown to user","learndash"),value:h||"",onChange:e=>x({label:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Class","learndash"),help:(0,a.__)("HTML class for link element","learndash"),value:m||"",onChange:e=>x({class_html:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Context","learndash"),help:(0,a.__)("User defined value to be passed into shortcode handler","learndash"),value:b||"",onChange:e=>x({context:e})}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Callback","learndash"),help:(0,a.__)("Custom callback function to be used instead of default output","learndash"),value:y||"",onChange:e=>x({callback:e})})));const D=(0,t.createElement)(r.InspectorControls,{key:"controls"},g(),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},k,T,(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:p||"",type:"number",onChange:function(e){x(""!=e&&e<0?{user_id:"0"}:{user_id:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Advanced","learndash"),initialOpen:!1},(0,t.createElement)(s.SelectControl,{key:"display_as",label:(0,a.__)("Displayed as","learndash"),help:(0,a.__)("Display as Button or Banner","learndash"),value:_||"button",options:[{label:(0,a.__)("Button","learndash"),value:"button"},{label:(0,a.sprintf)(
// translators: placeholders: Course, Group.
// translators: placeholders: Course, Group.
(0,a._x)("Banner (%1$s or %2$s only)","placeholders: Course, Group","learndash"),u("course"),u("group")),value:"banner"}],onChange:e=>x({display_as:e})}),P),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!f,onChange:e=>x({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID for preview.","learndash"),value:w||"",type:"number",onChange:function(e){x(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function I(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),ae)}function S(e){return I()}return[D,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:te,attributes:a,key:te,EmptyResponsePlaceholder:S})):I();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const le=(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("LearnDash %s Complete","placeholder: Quiz","learndash"),u("quiz"));(0,l.registerBlockType)("learndash/ld-quiz-complete",{title:le,description:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("This block shows the content if the user has completed the %s.","placeholder: Quiz","learndash"),u("quiz")),icon:"star-filled",category:"learndash-blocks",supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},quiz_id:{type:"string",default:""},user_id:{type:"string",default:""},autop:{type:"boolean",default:!0}},edit:e=>{const{attributes:{course_id:l,quiz_id:n,user_id:o,autop:i},className:d,setAttributes:p}=e,h=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s ID","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholders: Quiz, Quiz.
// translators: placeholders: Quiz, Quiz.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Quiz, Quiz","learndash"),u("quiz"),u("quiz")),value:n||"",type:"number",onChange:function(e){p(""!=e&&e<0?{quiz_id:"0"}:{quiz_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){p(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:o||"",type:"number",onChange:function(e){p(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),checked:!!i,onChange:e=>p({autop:e})})));let g="",m=_(n);return 0===m&&("sfwd-quiz"===c("post_type")&&(m=c("post_id"),m=_(m)),0==m&&(g=(0,a.sprintf)(
// translators: placeholders: Quiz, Quiz.
// translators: placeholders: Quiz, Quiz.
(0,a._x)("%1$s ID is required when not used within a %2$s.","placeholders: Quiz, Quiz","learndash"),u("quiz"),u("quiz")))),g.length&&(g=(0,t.createElement)("span",{className:"learndash-block-error-message"},g)),[h,(0,t.createElement)("div",{className:d,key:"ld-quiz-complete"},(0,t.createElement)("span",{className:"learndash-inner-header"},le),(0,t.createElement)("div",{className:"learndash-block-inner"},g,(0,t.createElement)(r.InnerBlocks,null)))]},save:e=>(0,t.createElement)(r.InnerBlocks.Content,null)});const re="learndash/ld-courseinfo",se=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Info [courseinfo]","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(re,{title:se,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block displays %s related information","placeholder: Course","learndash"),u("course")),icon:"analytics",category:"learndash-blocks",supports:{customClassName:!1},attributes:{show:{type:"string"},course_id:{type:"string",default:""},user_id:{type:"string",default:""},format:{type:"string"},seconds_format:{type:"string"},decimals:{type:"string"},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{course_id:l,show:n,user_id:i,format:d,seconds_format:p,decimals:_,preview_show:h,preview_user_id:g},className:m,setAttributes:b}=e,y=(0,t.createElement)(s.SelectControl,{key:"show",value:n||"course_title",label:(0,a.__)("Show","learndash"),options:[{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Title","placeholder: Course","learndash"),u("course")),value:"course_title"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s URL","placeholder: Course","learndash"),u("course")),value:"course_url"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Points","placeholder: Course","learndash"),u("course")),value:"course_points"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Price","placeholder: Course","learndash"),u("course")),value:"course_price"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Price Type","placeholder: Course","learndash"),u("course")),value:"course_price_type"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Enrolled Users Count","placeholder: Course","learndash"),u("course")),value:"course_users_count"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Total User %s Points","placeholder: Course","learndash"),u("course")),value:"user_course_points"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Total User %s Time","placeholder: Course","learndash"),u("course")),value:"user_course_time"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Completed On (date)","placeholder: Course","learndash"),u("course")),value:"completed_on"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Enrolled On (date)","placeholder: Course","learndash"),u("course")),value:"enrolled_on"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Cumulative %s Score","placeholder: Quizzes","learndash"),u("quizzes")),value:"cumulative_score"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Cumulative %s Points","placeholder: Quizzes","learndash"),u("quizzes")),value:"cumulative_points"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Possible Cumulative %s Total Points","placeholder: Quizzes","learndash"),u("quizzes")),value:"cumulative_total_points"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Cumulative %s Percentage","placeholder: Quizzes","learndash"),u("quizzes")),value:"cumulative_percentage"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Cumulative %s Time Spent","placeholder: Quizzes","learndash"),u("quizzes")),value:"cumulative_timespent"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Aggregate %s Percentage","placeholder: Quizzes","learndash"),u("quizzes")),value:"aggregate_percentage"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Aggregate %s Score","placeholder: Quizzes","learndash"),u("quizzes")),value:"aggregate_score"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Aggregate %s Points","placeholder: Quizzes","learndash"),u("quizzes")),value:"aggregate_points"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Possible Aggregate %s Total Points","placeholder: Quizzes","learndash"),u("quizzes")),value:"aggregate_total_points"},{label:(0,a.sprintf)(
// translators: placeholder: Quizzes.
// translators: placeholder: Quizzes.
(0,a._x)("Aggregate %s Time Spent","placeholder: Quizzes","learndash"),u("quizzes")),value:"aggregate_timespent"}],onChange:e=>b({show:e})}),f=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s or certificate.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){b(""!=e&&e<0?{course_id:"0"}:{course_id:e})}});let w="";["user_course_points","user_course_time","completed_on","enrolled_on","cumulative_score","cumulative_points","cumulative_total_points","cumulative_percentage","cumulative_timespent","aggregate_percentage","aggregate_score","aggregate_points","aggregate_total_points","aggregate_timespent"].includes(n)&&(w=(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:i||"",type:"number",onChange:function(e){b(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}));let v="";"completed_on"!=n&&"enrolled_on"!=n||(v=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Format","learndash"),help:(0,a.__)('This can be used to change the date format. Default: "F j, Y, g:i a.',"learndash"),value:d||"",onChange:e=>b({format:e})}));let C="";"user_course_time"==n&&(C=(0,t.createElement)(s.SelectControl,{key:"seconds_format",value:p,label:(0,a.__)("Seconds Format","learndash"),options:[{label:(0,a.__)("Time - 20min 49sec","learndash"),value:"time"},{label:(0,a.__)("Seconds - 1436","learndash"),value:"seconds"}],onChange:e=>b({seconds_format:e})}));let E="";"course_points"!=n&&"user_course_points"!=n||(E=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Decimals","learndash"),help:(0,a.__)("Number of decimal places to show. Default is 2.","learndash"),value:_||"",type:"number",onChange:function(e){b(""!=e&&e<0?{decimals:"0"}:{decimals:e})}}));const x=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!h,onChange:e=>b({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:g||"",type:"number",onChange:function(e){b(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function k(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),se)}function T(e){return k()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},f,w,y,v,C,E),x),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:re,attributes:a,key:re,EmptyResponsePlaceholder:T})):k();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const ne="learndash/ld-quizinfo",oe=(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("LearnDash %s Info [quizinfo]","placeholder: Quiz","learndash"),u("quiz"));(0,l.registerBlockType)(ne,{title:oe,description:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("This block displays %s related information","placeholder: Quiz","learndash"),u("quiz")),icon:"analytics",category:"learndash-blocks",supports:{customClassName:!1},attributes:{show:{type:"string",default:"quiz_title"},quiz_id:{type:"string",default:""},user_id:{type:"string",default:""},format:{type:"string"},field_id:{type:"string"},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{quiz_id:l,user_id:n,timestamp:i,show:d,format:p,field_id:_,preview_show:h,preview_user_id:g},className:m,setAttributes:b}=e,y=(0,t.createElement)(s.SelectControl,{key:"show",value:d||"quiz_title",label:(0,a.__)("Show","learndash"),options:[{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Title","placeholder: Quiz","learndash"),u("quiz")),value:"quiz_title"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Score","placeholder: Quiz","learndash"),u("quiz")),value:"score"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Count","placeholder: Quiz","learndash"),u("quiz")),value:"count"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Pass","placeholder: Quiz","learndash"),u("quiz")),value:"pass"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Timestamp","placeholder: Quiz","learndash"),u("quiz")),value:"timestamp"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Points","placeholder: Quiz","learndash"),u("quiz")),value:"points"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Total Points","placeholder: Quiz","learndash"),u("quiz")),value:"total_points"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Percentage","placeholder: Quiz","learndash"),u("quiz")),value:"percentage"},{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s Title","placeholder: Course","learndash"),u("course")),value:"course_title"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Time Spent","placeholder: Quiz","learndash"),u("quiz")),value:"timespent"},{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s Form Field","placeholder: Quiz","learndash"),u("quiz")),value:"field"}],onChange:e=>b({show:e})});let f="";"field"==d&&(f=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Custom Field ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("The Field ID is shown on the %s Custom Fields table.","placeholder: Quiz","learndash"),u("quiz")),value:_||"",onChange:e=>b({field_id:e})}));let w="";"timestamp"!=d&&"field"!=d||(w=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Format","learndash"),help:(0,a.__)("This can be used to change the date format. Default: F j, Y, g:i a.","learndash"),value:p||"",onChange:e=>b({format:e})}));const v=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},y,f,w,(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("%s ID","placeholder: Quiz","learndash"),u("quiz")),help:(0,a.sprintf)(
// translators: placeholders: Quiz, Quiz.
// translators: placeholders: Quiz, Quiz.
(0,a._x)("Enter a single %1$s ID. Leave blank if used within a %2$s or Certificate.","placeholders: Quiz, Quiz","learndash"),u("quiz"),u("quiz")),value:l||"",type:"number",onChange:function(e){b(""!=e&&e<0?{quiz_id:"0"}:{quiz_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)("Enter a single User ID. Leave blank if used within a %s or Certificate.","placeholder: Quiz","learndash"),u("quiz")),value:n||"",type:"number",onChange:function(e){b(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Attempt timestamp","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Quiz.
// translators: placeholder: Quiz.
(0,a._x)('Single %s attempt timestamp. See WP user profile "#" link on attempt row. Leave blank to use latest attempt or within a Certificate.',"placeholder: Quiz","learndash"),u("quiz")),value:i||"",onChange:function(e){if(e.length&&e.startsWith("data:quizinfo:",0)){var t=e.split(":");if(t.length>2){var a="";for(let e=2;e<t.length;e++)""!=a?("quiz_id"==a?b({quiz_id:t[e]}):"user_id"==a?b({user_id:t[e]}):"time"==a&&b({timestamp:t[e]}),a=""):"quiz"==t[e]?a="quiz_id":"user"==t[e]?a="user_id":"time"==t[e]&&(a="time")}}}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!h,onChange:e=>b({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:g||"",type:"number",onChange:function(e){b(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function C(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),oe)}function E(e){return C()}return[v,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:ne,attributes:a,key:ne,EmptyResponsePlaceholder:E})):C();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const ie="learndash/ld-groupinfo",de=(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("LearnDash %s Info [groupinfo]","placeholder: Group","learndash"),u("group"));(0,l.registerBlockType)(ie,{title:de,description:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("This block displays %s related information","placeholder: Group","learndash"),u("group")),icon:"analytics",category:"learndash-blocks",supports:{customClassName:!1},attributes:{show:{type:"string"},group_id:{type:"string",default:""},user_id:{type:"string",default:""},format:{type:"string"},decimals:{type:"string"},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{group_id:l,show:n,user_id:i,format:d,decimals:p,preview_show:_,preview_user_id:h},setAttributes:g}=e,m=(0,t.createElement)(s.SelectControl,{key:"show",value:n,label:(0,a.__)("Show","learndash"),options:[{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Title","placeholder: Group","learndash"),u("group")),value:"group_title"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s URL","placeholder: Group","learndash"),u("group")),value:"group_url"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Price","placeholder: Group","learndash"),u("group")),value:"group_price"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Price Type","placeholder: Group","learndash"),u("group")),value:"group_price_type"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Enrolled Users Count","placeholder: Group","learndash"),u("group")),value:"group_users_count"},{label:(0,a.sprintf)(
// translators: placeholders: Group, Courses.
// translators: placeholders: Group, Courses.
(0,a._x)("%1$s %2$s Count","placeholders: Group, Courses","learndash"),u("group"),u("courses")),value:"group_courses_count"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("User %s Status","placeholder: Group","learndash"),u("group")),value:"user_group_status"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Completed On (date)","placeholder: Group","learndash"),u("group")),value:"completed_on"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Enrolled On (date)","placeholder: Group","learndash"),u("group")),value:"enrolled_on"},{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s Completed Percentage","placeholder: Group","learndash"),u("group")),value:"percent_completed"}],onChange:e=>g({show:e})}),b=(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:(0,a.sprintf)(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group")),value:l||"",type:"number",onChange:function(e){g(""!=e&&e<0?{group_id:"0"}:{group_id:e})}});let y="";["user_group_status","completed_on","enrolled_on","percent_completed"].includes(n)&&(y=(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:i||"",type:"number",onChange:function(e){g(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}));let f="";["completed_on","enrolled_on"].includes(n)&&(f=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Format","learndash"),help:(0,a.__)('This can be used to change the date format. Default: "F j, Y, g:i a.',"learndash"),value:d||"",onChange:e=>g({format:e})}));let w="";["percent_completed"].includes(n)&&(w=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Decimals","learndash"),help:(0,a.__)("Number of decimal places to show. Default is 2.","learndash"),value:p||"",type:"number",onChange:function(e){g(""!=e&&e<0?{decimals:"0"}:{decimals:e})}}));const v=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!_,onChange:e=>g({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:h||"",type:"number",onChange:function(e){g(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function C(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),de)}function E(e){return C()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},b,y,m,f,w),v),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:ie,attributes:a,key:ie,EmptyResponsePlaceholder:E})):C();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const ue="learndash/ld-usermeta",ce=(0,a.__)("LearnDash User meta","learndash");(0,l.registerBlockType)(ue,{title:ce,description:(0,a.__)("This block displays User meta field","learndash"),icon:"id",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{field:{type:"string",default:"user_login"},user_id:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{field:l,user_id:n,preview_show:i,preview_user_id:d},setAttributes:u}=e,p=(0,t.createElement)(s.SelectControl,{key:"field",value:l,label:(0,a.__)("Field","learndash"),options:[{label:(0,a.__)("User Login","learndash"),value:"user_login"},{label:(0,a.__)("User First Name","learndash"),value:"first_name"},{label:(0,a.__)("User Last Name","learndash"),value:"last_name"},{label:(0,a.__)("User First and Last Name","learndash"),value:"first_last_name"},{label:(0,a.__)("User Display Name","learndash"),value:"display_name"},{label:(0,a.__)("User Nicename","learndash"),value:"user_nicename"},{label:(0,a.__)("User Nickname","learndash"),value:"nickname"},{label:(0,a.__)("User Email","learndash"),value:"user_email"},{label:(0,a.__)("User URL","learndash"),value:"user_url"},{label:(0,a.__)("User Description","learndash"),value:"description"}],onChange:e=>u({field:e})}),_=(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:n||"",type:"number",onChange:function(e){u(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),h=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!i,onChange:e=>u({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:d||"",type:"number",onChange:function(e){u(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function g(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),ce)}function m(e){return g()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},_,p),h),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:ue,attributes:a,key:ue,EmptyResponsePlaceholder:m})):g();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const pe="learndash/ld-registration",_e=(0,a.__)("LearnDash Registration","learndash");(0,l.registerBlockType)(pe,{title:_e,description:(0,a.__)("Shows the registration form","learndash"),icon:"id-alt",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{width:{type:"string"},example_show:{type:"boolean",default:1},preview_show:{type:"boolean",default:!0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{preview_show:l,example_show:n,width:i},setAttributes:d}=e,u=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Styling","learndash"),initialOpen:!0},(0,t.createElement)(s.TextControl,{label:(0,a.__)("Form Width","learndash"),help:(0,a.__)("Sets the width of the registration form.","learndash"),value:i||"",type:"string",onChange:e=>d({width:e})})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!l,onChange:e=>d({preview_show:e})})));function p(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),_e)}function _(e){return p()}return[u,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:pe,attributes:a,key:pe,EmptyResponsePlaceholder:_})):p();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const he="learndash/ld-infobar",ge=(0,a.__)("LearnDash Infobar","learndash");(0,l.registerBlockType)(he,{title:ge,description:(0,a.__)("This block displays an Infobar for a specific LearnDash related post.","learndash"),icon:"welcome-widgets-menus",category:"learndash-blocks",supports:{customClassName:!1},attributes:{display_type:{type:"string",default:""},course_id:{type:"string",default:""},post_id:{type:"string",default:""},group_id:{type:"string",default:""},user_id:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_user_id:{type:"string",default:""},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{display_type:l,course_id:n,post_id:i,group_id:d,user_id:p,preview_show:_,preview_user_id:h},setAttributes:m}=e;var b,y;b=(0,t.createElement)(s.SelectControl,{key:"display_type",label:(0,a.__)("Display Type","learndash"),value:l,help:sprintf(
// translators: placeholders: Course, Group.
// translators: placeholders: Course, Group.
(0,a._x)("Require if not used within a %1$s or %2$s. Or to override default display.","placeholders: Course, Group","learndash"),u("course"),u("group")),options:[{label:(0,a.__)("Select a Display Type","learndash"),value:""},{label:u("course"),value:"sfwd-courses"},{label:u("group"),value:"groups"}],onChange:e=>m({display_type:e})}),"sfwd-courses"===l?(m({group_id:""}),y=(0,t.createElement)(React.Fragment,null,(0,t.createElement)(s.TextControl,{label:sprintf(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:sprintf(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:n||"",type:"number",onChange:function(e){m(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Step ID","learndash"),help:sprintf(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single Step ID. Leave blank if used within a %1$s step.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:i||"",type:"number",onChange:function(e){m(""!=e&&e<0?{post_id:"0"}:{post_id:e})}}))):"groups"===l&&(m({course_id:""}),m({post_id:""}),y=(0,t.createElement)(s.TextControl,{label:sprintf(
// translators: placeholder: Group.
// translators: placeholder: Group.
(0,a._x)("%s ID","placeholder: Group","learndash"),u("group")),help:sprintf(
// translators: placeholders: Group, Group.
// translators: placeholders: Group, Group.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Group, Group","learndash"),u("group"),u("group")),value:d||"",type:"number",onChange:function(e){m(""!=e&&e<0?{group_id:"0"}:{group_id:e})}}));const f=(0,t.createElement)(r.InspectorControls,{key:"controls"},g(),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},b,y,(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("Enter specific User ID. Leave blank for current User.","learndash"),value:p||"",type:"number",onChange:function(e){m(""!=e&&e<0?{user_id:"0"}:{user_id:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!_,onChange:e=>m({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID for preview.","learndash"),value:h||"",type:"number",onChange:function(e){m(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}})));function w(){return sprintf(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),ge)}function v(e){return w()}return[f,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:he,attributes:a,key:he,EmptyResponsePlaceholder:v})):w();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const me="learndash/ld-materials",be=(0,a.__)("LearnDash Materials","learndash");(0,l.registerBlockType)(me,{title:be,description:(0,a.__)("This block displays the materials for a specific LearnDash related post.","learndash"),icon:"text",category:"learndash-blocks",supports:{customClassName:!1},attributes:{post_id:{type:"string",default:""},autop:{type:"string",default:"true"},preview_show:{type:"boolean",default:1},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{post_id:l,autop:n,preview_show:i},setAttributes:d}=e,u=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Post ID","learndash"),help:(0,a.__)("Enter a Post ID of the LearnDash post that you want to display materials for.","learndash"),value:l||"",type:"number",onChange:function(e){d(""!=e&&e<0?{post_id:"0"}:{post_id:e})}}),p=(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Auto Paragraph","learndash"),help:(0,a.__)("Whether to format materials content using wpautop.","learndash"),checked:!!n,onChange:e=>d({autop:e})}),_=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!i,onChange:e=>d({preview_show:e})}));function h(){return sprintf(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),be)}function m(e){return h()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},g(),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},u,p),_),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:me,attributes:a,key:me,EmptyResponsePlaceholder:m})):h();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const ye="learndash/ld-user-status",fe=(0,a.__)("LearnDash User Status");(0,l.registerBlockType)(ye,{title:fe,description:(0,a.__)("This block displays information of enrolled courses and their progress for a user. Defaults to current logged in user if no ID specified.","learndash"),icon:"analytics",category:"learndash-blocks",supports:{customClassName:!1},attributes:{user_id:{type:"string",default:""},registered_num:{type:"string",default:""},registered_order_by:{type:"string"},registered_order:{type:"string"},preview_show:{type:"boolean",default:!0},preview_user_id:{type:"string",default:""},isblock:{type:"boolean",default:1},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{user_id:l,registered_num:n,registered_order_by:i,registered_order:d,preview_show:u,preview_user_id:p,isblock:_},setAttributes:h}=e,m=(0,t.createElement)(s.TextControl,{label:(0,a.__)("User ID","learndash"),help:(0,a.__)("ID of the user to display information for.","learndash"),value:l||"",type:"number",onChange:function(e){h(""!=e&&e<0?{user_id:"0"}:{user_id:e})}}),b=(0,t.createElement)(s.TextControl,{label:(0,a.__)("Courses per page","learndash"),help:(0,a.__)("Number of courses to display per page. Set to 0 for no pagination.","learndash"),value:n||"",type:"number",onChange:function(e){h(""!=e&&e<0?{registered_num:"0"}:{registered_num:e})}}),y=(0,t.createElement)(s.SelectControl,{key:"registered_order_by",value:i,label:(0,a.__)("Order By","learndash"),options:[{label:(0,a.__)("Title","learndash"),value:"post_title"},{label:(0,a.__)("ID","learndash"),value:"post_id"},{label:(0,a.__)("Date","learndash"),value:"post_date"},{label:(0,a.__)("Menu","learndash"),value:"menu_order"}],onChange:e=>h({registered_order_by:e})}),f=(0,t.createElement)(s.SelectControl,{key:"registered_order",value:d,label:(0,a.__)("Order","learndash"),options:[{label:(0,a.__)("ASC (default)","learndash"),value:"ASC"},{label:(0,a.__)("DESC","learndash"),value:"DESC"}],onChange:e=>h({registered_order:e})}),w=(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!u,onChange:e=>h({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Preview User ID","learndash"),help:(0,a.__)("Enter a User ID to test preview","learndash"),value:p||"",type:"number",onChange:function(e){h(""!=e&&e<0?{preview_user_id:"0"}:{preview_user_id:e})}}));function v(){return sprintf(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),fe)}function C(e){return v()}return[(0,t.createElement)(r.InspectorControls,{key:"controls"},g(),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},m,b,y,f),w),(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:ye,attributes:a,key:ye,EmptyResponsePlaceholder:C})):v();var a}),[e.attributes])]},save:function(e){delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const we="learndash/ld-navigation",ve=(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("LearnDash %s Navigation","placeholder: Course","learndash"),u("course"));(0,l.registerBlockType)(we,{title:ve,description:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("This block displays the %s Navigation.","placeholder: Course","learndash"),u("course")),icon:"format-aside",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{course_id:{type:"string",default:""},post_id:{type:"string",default:""},preview_show:{type:"boolean",default:1},preview_post_id:{type:"string",default:""},example_show:{type:"boolean",default:0},editing_post_meta:{type:"object"}},edit:e=>{const{attributes:{course_id:l,post_id:n,preview_show:i,preview_post_id:d,example_show:p},className:_,setAttributes:h}=e,m=(0,t.createElement)(r.InspectorControls,{key:"controls"},g(),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Settings","learndash")},(0,t.createElement)(s.TextControl,{label:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("%s ID","placeholder: Course","learndash"),u("course")),help:(0,a.sprintf)(
// translators: placeholders: Course, Course.
// translators: placeholders: Course, Course.
(0,a._x)("Enter single %1$s ID. Leave blank if used within a %2$s.","placeholders: Course, Course","learndash"),u("course"),u("course")),value:l||"",type:"number",onChange:function(e){h(""!=e&&e<0?{course_id:"0"}:{course_id:e})}}),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Step ID","learndash"),help:(0,a.sprintf)(
// translators: placeholder: Course.
// translators: placeholder: Course.
(0,a._x)("Enter single Step ID. Leave blank if used within a %s.","placeholder: Course","learndash"),u("course")),value:n||"",type:"number",onChange:function(e){h(""!=e&&e<0?{post_id:"0"}:{post_id:e})}})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!i,onChange:e=>h({preview_show:e})}),(0,t.createElement)(s.PanelRow,{className:"learndash-block-error-message"},(0,a.__)("Preview settings are not saved.","learndash")),(0,t.createElement)(s.TextControl,{label:(0,a.__)("Step ID","learndash"),help:(0,a.__)("Enter a Step ID to test preview","learndash"),value:d||"",type:"number",onChange:function(e){h(""!=e&&e<0?{preview_post_id:"0"}:{preview_post_id:e})}})));function b(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),ve)}function y(e){return b()}return[m,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:we,attributes:a,key:we,EmptyResponsePlaceholder:y})):b();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const Ce="learndash/ld-reset-password",Ee=(0,a.__)("LearnDash Reset Password","learndash");(0,l.registerBlockType)(Ce,{title:Ee,description:(0,a.__)("Shows the reset password form","learndash"),icon:"id-alt",category:"learndash-blocks",example:{attributes:{example_show:1}},supports:{customClassName:!1},attributes:{width:{type:"string"},example_show:{type:"boolean",default:1},preview_show:{type:"boolean",default:!0},editing_post_meta:{type:"object"}},edit:function(e){const{attributes:{preview_show:l,example_show:n,width:i},setAttributes:d}=e,u=(0,t.createElement)(r.InspectorControls,{key:"controls"},(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Styling","learndash"),initialOpen:!0},(0,t.createElement)(s.TextControl,{label:(0,a.__)("Form Width","learndash"),help:(0,a.__)("Sets the width of the reset password form.","learndash"),value:i||"",type:"string",onChange:e=>d({width:e})})),(0,t.createElement)(s.PanelBody,{title:(0,a.__)("Preview","learndash"),initialOpen:!1},(0,t.createElement)(s.ToggleControl,{label:(0,a.__)("Show Preview","learndash"),checked:!!l,onChange:e=>d({preview_show:e})})));function p(){return(0,a.sprintf)(
// translators: placeholder: block_title.
// translators: placeholder: block_title.
(0,a._x)("%s block output shown here","placeholder: block_title","learndash"),Ee)}function _(e){return p()}return[u,(0,t.useMemo)((()=>{return 1==(a=e.attributes).preview_show?(a.editing_post_meta=c(),(0,t.createElement)(o(),{block:Ce,attributes:a,key:Ce,EmptyResponsePlaceholder:_})):p();var a}),[e.attributes])]},save:e=>{delete e.attributes.example_show,delete e.attributes.editing_post_meta}});const xe=window.wp.data,ke=window.React;var Te=e.n(ke);const Pe=(0,ke.createContext)({}),De={block_key:"learndash/ld-exam",block_title:(0,a.sprintf)(
// translators: placeholder: Challenge Exam.
// translators: placeholder: Challenge Exam.
(0,a._x)("LearnDash %s","placeholder: Challenge Exam","learndash"),u("exam")),block_description:(0,a.sprintf)(
// translators: placeholder: Create a Challenge Exam.
// translators: placeholder: Create a Challenge Exam.
(0,a._x)("Create a %s","placeholder: Create a Challenge Exam","learndash"),u("exam"))};(0,l.registerBlockType)(De.block_key,{title:De.block_title,description:De.block_description,icon:"editor-help",category:"learndash-blocks",supports:{html:!1},attributes:{ld_version:{type:"string"}},edit:e=>{const{attributes:{ld_version:a=""},setAttributes:l,clientId:s}=e,n=(0,xe.useSelect)((e=>e("core/block-editor").getBlockOrder(s)),[]),o=(0,t.useMemo)((()=>({blockOrder:n})),[s,n]);return""===a&&l({ld_version:ldlms_settings.version}),(0,t.createElement)(Pe.Provider,{value:o},(0,t.createElement)(r.InnerBlocks,{allowedBlocks:["learndash/ld-exam-question"],template:[["learndash/ld-exam-question",{}]],renderAppender:()=>(0,t.createElement)(r.ButtonBlockAppender,{className:"ld-exam-block-appender",rootClientId:s}),templateInsertUpdatesSelection:!0}))},save:()=>(0,t.createElement)(r.InnerBlocks.Content,null)});var Ie={color:void 0,size:void 0,className:void 0,style:void 0,attr:void 0},Se=Te().createContext&&Te().createContext(Ie),ze=function(){return ze=Object.assign||function(e){for(var t,a=1,l=arguments.length;a<l;a++)for(var r in t=arguments[a])Object.prototype.hasOwnProperty.call(t,r)&&(e[r]=t[r]);return e},ze.apply(this,arguments)};function qe(e){return e&&e.map((function(e,t){return Te().createElement(e.tag,ze({key:t},e.attr),qe(e.child))}))}function Be(e){return function(t){return Te().createElement(Le,ze({attr:ze({},e.attr)},t),qe(e.child))}}function Le(e){var t=function(t){var a,l=e.attr,r=e.size,s=e.title,n=function(e,t){var a={};for(var l in e)Object.prototype.hasOwnProperty.call(e,l)&&t.indexOf(l)<0&&(a[l]=e[l]);if(null!=e&&"function"==typeof Object.getOwnPropertySymbols){var r=0;for(l=Object.getOwnPropertySymbols(e);r<l.length;r++)t.indexOf(l[r])<0&&Object.prototype.propertyIsEnumerable.call(e,l[r])&&(a[l[r]]=e[l[r]])}return a}(e,["attr","size","title"]),o=r||t.size||"1em";return t.className&&(a=t.className),e.className&&(a=(a?a+" ":"")+e.className),Te().createElement("svg",ze({stroke:"currentColor",fill:"currentColor",strokeWidth:"0"},t.attr,l,n,{className:a,style:ze(ze({color:e.color||t.color},t.style),e.style),height:o,width:o,xmlns:"http://www.w3.org/2000/svg"}),s&&Te().createElement("title",null,s),e.children)};return void 0!==Se?Te().createElement(Se.Consumer,null,(function(e){return t(e)})):t(Ie)}function Oe(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0z"}},{tag:"path",attr:{d:"M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"}}]})(e)}function Ne(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0z"}},{tag:"path",attr:{d:"M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"}}]})(e)}function Ue(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0V0z"}},{tag:"path",attr:{d:"M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6z"}},{tag:"path",attr:{d:"M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-5.99 13c-.59 0-1.05-.47-1.05-1.05 0-.59.47-1.04 1.05-1.04.59 0 1.04.45 1.04 1.04-.01.58-.45 1.05-1.04 1.05zm2.5-6.17c-.63.93-1.23 1.21-1.56 1.81-.13.24-.18.4-.18 1.18h-1.52c0-.41-.06-1.08.26-1.65.41-.73 1.18-1.16 1.63-1.8.48-.68.21-1.94-1.14-1.94-.88 0-1.32.67-1.5 1.23l-1.37-.57C11.51 5.96 12.52 5 13.99 5c1.23 0 2.08.56 2.51 1.26.37.61.58 1.73.01 2.57z"}}]})(e)}function Ae(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0z"}},{tag:"path",attr:{d:"M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"}}]})(e)}function $e(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{fill:"none",d:"M0 0h24v24H0z"}},{tag:"path",attr:{d:"M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"}}]})(e)}function Ge(e){return Be({tag:"svg",attr:{viewBox:"0 0 1024 1024"},child:[{tag:"path",attr:{d:"M955.7 856l-416-720c-6.2-10.7-16.9-16-27.7-16s-21.6 5.3-27.7 16l-416 720C56 877.4 71.4 904 96 904h832c24.6 0 40-26.6 27.7-48zM480 416c0-4.4 3.6-8 8-8h48c4.4 0 8 3.6 8 8v184c0 4.4-3.6 8-8 8h-48c-4.4 0-8-3.6-8-8V416zm32 352a48.01 48.01 0 0 1 0-96 48.01 48.01 0 0 1 0 96z"}}]})(e)}const Re=[{label:(0,a.__)("Single","learndash"),value:"single"},{label:(0,a.__)("Multiple","learndash"),value:"multiple"}],Qe=(0,a.__)("The Question is empty.","learndash"),Me=sprintf(
// translators: placeholder: Question type.
// translators: placeholder: Question type.
(0,a._x)("%s type","placeholder: Question type","learndash"),u("question")),We={block_key:"learndash/ld-exam-question",block_title:(0,a.sprintf)(
// translators: placeholder: Challenge Exam Question.
// translators: placeholder: Challenge Exam Question.
(0,a._x)("%s Question","placeholder: Challenge Exam Question","learndash"),u("exam")),block_description:(0,a.sprintf)(
// translators: placeholder: Create a question for your Challenge Exam.
// translators: placeholder: Create a question for your Challenge Exam.
(0,a._x)("Create a question for your %s","placeholder: Create a question for your Challenge Exam","learndash"),u("exam"))};function je(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{d:"M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20Z"}}]})(e)}function Fe(e){return Be({tag:"svg",attr:{viewBox:"0 0 24 24"},child:[{tag:"path",attr:{d:"M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM11.0026 16L18.0737 8.92893L16.6595 7.51472L11.0026 13.1716L8.17421 10.3431L6.75999 11.7574L11.0026 16Z"}}]})(e)}(0,l.registerBlockType)(We.block_key,{title:We.block_title,description:We.block_description,icon:(0,t.createElement)(Oe,null),category:"learndash-blocks",parent:["learndash/ld-exam"],supports:{html:!1},providesContext:{"learndash/question_type":"question_type"},attributes:{question_title:{type:"string"},question_type:{type:"string"}},edit:e=>{const{attributes:{question_title:l="",question_type:n=""},setAttributes:o,clientId:i}=e,[d,u]=(0,t.useState)(!1),[c,p]=(0,t.useState)(!1),{innerBlocksClientIds:_,selectedBlockClientId:h}=(0,xe.useSelect)((e=>({innerBlocksClientIds:e("core/block-editor").getClientIdsOfDescendants([i]),selectedBlockClientId:e("core/block-editor").getSelectedBlockClientId()}))),g=_.includes(h)||h===i,{blockOrder:m}=(0,t.useContext)(Pe),b=m.lastIndexOf(i)===m.length-1;!1===c&&!0===g&&p(!0),!0===c&&!1===d&&!1===g&&u(!0),""===n&&o({question_type:"single"});const y=d?"learndash-exam-question-allow-validations":"";return(0,t.createElement)(t.Fragment,null,(0,t.createElement)(r.InspectorControls,null,(0,t.createElement)(s.PanelBody,{title:Me,initialOpen:!0},(0,t.createElement)(s.PanelRow,null,(0,t.createElement)(s.SelectControl,{value:n,options:Re,onChange:e=>o({question_type:e})})))),(0,t.createElement)(r.PlainText,{className:"learndash-exam-question",value:l,placeholder:(0,a.__)("Question","learndash"),onChange:e=>o({question_title:e})}),0===l.length&&(0,t.createElement)("div",{className:`${y} learndash-exam-question-empty-title`},(0,t.createElement)(Ge,{fill:"red"}),(0,t.createElement)("span",null,Qe)),(0,t.createElement)("div",{className:`${y} learndash-exam-question-flexbox`},(0,t.createElement)(r.InnerBlocks,{template:[["learndash/ld-question-description",{}],["learndash/ld-question-answers-block",{}],["learndash/ld-correct-answer-message-block",{}],["learndash/ld-incorrect-answer-message-block",{}]],templateLock:"all"})),!b&&(0,t.createElement)("hr",null))},save:()=>(0,t.createElement)(r.InnerBlocks.Content,null)});const Ve=e=>{const{isMultiple:l,attributes:{answer_label:n="",answer_correct:o=!1},setAttributes:i}=e;return(0,t.createElement)(t.Fragment,null,(0,t.createElement)("span",{className:"learndash-exam-question-answer-select"},l?o?(0,t.createElement)($e,null):(0,t.createElement)(Ae,null):o?(0,t.createElement)(Fe,null):(0,t.createElement)(je,null)),(0,t.createElement)("span",{className:"learndash-exam-question-answer-input"},(0,t.createElement)(r.RichText,{value:n,placeholder:(0,a.__)("Add Answer","learndash"),onChange:e=>i({answer_label:e})})),(0,t.createElement)("span",{className:"learndash-exam-question-answer-toggle"},(0,t.createElement)(s.Button,{isSmall:!0,className:"learndash-exam-question-answer-toggle-button",variant:o?"primary":"secondary",disabled:0===n.length,onClick:()=>i({answer_correct:!o}),text:o?(0,a.__)("Correct","learndash"):(0,a.__)("Incorrect","learndash")})))},He=e=>{const{type:l,attributes:r,setAttributes:n}=e,o=(0,a.__)("Answer is missing.","learndash"),i=(0,a.__)("Required correct answer is missing.","learndash"),d="multiple"===l,u=r.map((({answer_correct:e})=>null!=e&&e)).lastIndexOf(!0);let c=0,p=!1;const _=Array.isArray(r)&&r.length>=1?[...r.filter((e=>""!==e.label)).map(((e,t)=>(c=!0===e.answer_correct?c+1:c,p=!0,d||t===u?{...e}:{...e,answer_correct:!1})))]:[{answer_label:"",answer_correct:!0},{answer_label:"",answer_correct:!1}];!d&&1<c&&n(_),""!==_[_.length-1].answer_label&&_.push({answer_label:"",answer_correct:!1});const h=e=>{if(e>_.length)return;const t=[..._];t.splice(e,1),n(t.filter((({answer_label:e})=>""!==e)))};return(0,t.createElement)(t.Fragment,null,(0,t.createElement)("div",{className:"learndash-exam-question-answers-list"},(0,t.createElement)("div",{className:"learndash-exam-question-single-answer"},!p&&(0,t.createElement)("div",{className:"learndash-exam-question-empty-answers"},(0,t.createElement)(Ge,{fill:"red"}),(0,t.createElement)("span",null,o)),p&&0===c&&(0,t.createElement)("div",{className:"learndash-exam-question-empty-correct"},(0,t.createElement)(Ge,{fill:"red"}),(0,t.createElement)("span",null,i))),_.length&&_.map(((e,a)=>(0,t.createElement)("div",{className:"learndash-exam-question-single-answer",key:a},(0,t.createElement)("span",{className:"learndash-exam-question-single-answer-delete"},_.length-1!==a&&(0,t.createElement)(s.Button,{isSmall:!0,onClick:()=>h(a),icon:(0,t.createElement)(Ne,null)})),(0,t.createElement)(Ve,{isMultiple:d,attributes:e,setAttributes:e=>((e,t)=>{if(""===t.answer_label)h(e);else{const a=_.map((e=>!0!==t.answer_correct||d?{...e}:{...e,answer_correct:!1}));a[e]={..._[e],...t},n(a.filter((({answer_label:e})=>""!==e)))}})(a,e)}))))))},Ze={single:e=>(0,t.createElement)(He,e),multiple:e=>(0,t.createElement)(He,e)},Ye=(0,a.sprintf)(
// translators: placeholder: Challenge Exam Question Answers.
// translators: placeholder: Challenge Exam Question Answers.
(0,a._x)("%s Question Answers","placeholder: Challenge Exam Question Answers","learndash"),u("exam")),Je=(0,a.sprintf)(
// translators: placeholder: Challenge Exam Question Answers.
// translators: placeholder: Challenge Exam Question Answers.
(0,a._x)("%s Question Answers","placeholder: Challenge Exam Question Answers","learndash"),u("exam"));(0,l.registerBlockType)("learndash/ld-question-answers-block",{title:Ye,description:Je,icon:(0,t.createElement)(Ue,null),category:"learndash-blocks",parent:["learndash/ld-exam-question"],usesContext:["learndash/question_type"],attributes:{question_type:{type:"string",default:""},answers:{type:"array",default:[]}},supports:{inserter:!1,html:!1},edit:e=>{const{attributes:{answers:a},setAttributes:l,context:r,clientId:s}=e,n="learndash/question_type"in r&&r["learndash/question_type"]?r["learndash/question_type"]:"single",o=Ze[n];return l({question_type:n}),(0,t.createElement)(o,{clientId:s,type:n,attributes:[...a],setAttributes:e=>l({answers:[...e]})})},save:()=>(0,t.createElement)(r.InnerBlocks.Content,null)});const Ke={icon:(0,t.createElement)(Ue,null),parent:["learndash/ld-exam-question"],category:"learndash-blocks",supports:{inserter:!1,html:!1},save:()=>(0,t.createElement)(r.InnerBlocks.Content,null)},Xe=["core/image","core/paragraph"],et=((0,l.registerBlockType)("learndash/ld-incorrect-answer-message-block",{...Ke,title:(0,a.__)("Incorrect answer message","learndash"),description:(0,a.__)("Incorrect answer message","learndash"),edit:()=>{const e=[["core/paragraph",{placeholder:(0,a.__)("Add a message for incorrect answer (Optional)","learndash")}]];return(0,t.createElement)(t.Fragment,null,(0,t.createElement)("div",null,(0,a.__)("Incorrect Answer Message","learndash")),(0,t.createElement)(r.InnerBlocks,{allowedBlocks:Xe,template:e,templateLock:!1}))}}),(0,l.registerBlockType)("learndash/ld-correct-answer-message-block",{...Ke,title:(0,a.__)("Correct answer message","learndash"),description:(0,a.__)("Correct answer message","learndash"),edit:()=>{const e=[["core/paragraph",{placeholder:(0,a.__)("Add a message for correct answer (Optional)","learndash")}]];return(0,t.createElement)(t.Fragment,null,(0,t.createElement)("div",null,(0,a.__)("Correct Answer Message","learndash")),(0,t.createElement)(r.InnerBlocks,{allowedBlocks:Xe,template:e,templateLock:!1}))}}),(0,a.__)("Question Notes","learndash")),tt=(0,a.sprintf)(
// translators: placeholder: Write a description for the Challenge Exam question.
// translators: placeholder: Write a description for the Challenge Exam question.
(0,a._x)("Write a description for the %s question.","placeholder: Write a description for the Challenge Exam question","learndash"),u("exam"));(0,l.registerBlockType)("learndash/ld-question-description",{title:et,description:tt,icon:(0,t.createElement)(Oe,null),parent:["learndash/ld-exam-question"],category:"learndash-blocks",supports:{inserter:!1,html:!1},edit:()=>{const e=[["core/paragraph",{placeholder:(0,a.__)("Add a Description or type '/' to choose a block (Optional)","learndash")}]];return(0,t.createElement)(r.InnerBlocks,{templateLock:!1,template:e})},save:()=>(0,t.createElement)(r.InnerBlocks.Content,null)})})();