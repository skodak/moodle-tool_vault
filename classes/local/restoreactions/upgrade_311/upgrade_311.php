<?php
// This file is part of plugin tool_vault - https://lmsvault.io
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_vault\local\restoreactions\upgrade_311;
use tool_vault\api;
use tool_vault\constants;
use tool_vault\site_restore;

/**
 * Class plugins
 *
 * @package    tool_vault
 * @copyright  2024 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upgrade_311 {
    /**
     * Upgrade the restored site to 3.11.8
     *
     * @param site_restore $logger
     * @return void
     */
    public static function upgrade(site_restore $logger) {
        self::upgrade_core($logger);
        self::upgrade_plugins($logger);
        set_config('upgraderunning', 0);
    }

    /**
     * Upgrade core to 3.11.8
     *
     * @param site_restore $logger
     * @return void
     */
    protected static function upgrade_core(site_restore $logger) {
        global $CFG;
        require_once(__DIR__ ."/core.php");

        try {
            tool_vault_311_core_upgrade($CFG->version);
        } catch (\Throwable $t) {
            $logger->add_to_log("Exception executing core upgrade script: ".
               $t->getMessage(), constants::LOGLEVEL_WARNING);
            api::report_error($t);
        }

        set_config('version', 2021051708.00);
        set_config('release', '3.11.8');
        set_config('branch', '311');
    }

    /**
     * Upgrade all standard plugins to 3.11.8
     *
     * @param site_restore $logger
     * @return void
     */
    protected static function upgrade_plugins(site_restore $logger) {
        global $DB;
        $allcurversions = $DB->get_records_menu('config_plugins', ['name' => 'version'], '', 'plugin, value');
        foreach (self::plugin_versions() as $plugin => $version) {
            if (empty($allcurversions[$plugin])) {
                // Standard plugin {$plugin} not found. It will be installed during the full upgrade.
                continue;
            }
            if (file_exists(__DIR__ ."/". $plugin .".php")) {
                require_once(__DIR__ ."/". $plugin .".php");
                $pluginshort = preg_replace("/^mod_/", "", $plugin);
                $funcname = "tool_vault_311_xmldb_{$pluginshort}_upgrade";
                try {
                    $funcname($allcurversions[$plugin]);
                } catch (\Throwable $t) {
                    $logger->add_to_log("Exception executing upgrade script for plugin {$plugin}: ".
                        $t->getMessage(), constants::LOGLEVEL_WARNING);
                    api::report_error($t);
                }
            }
            set_config('version', $version, $plugin);
        }
    }

    /**
     * List of standard plugins in 3.11.8 and their exact versions
     *
     * @return array
     */
    protected static function plugin_versions() {
        return [
            "mod_assign" => 2021051700,
            "mod_assignment" => 2021051700,
            "mod_book" => 2021051700,
            "mod_chat" => 2021051700,
            "mod_choice" => 2021051700,
            "mod_data" => 2021051700,
            "mod_feedback" => 2021051700,
            "mod_folder" => 2021051700,
            "mod_forum" => 2021051701,
            "mod_glossary" => 2021051700,
            "mod_h5pactivity" => 2021051700,
            "mod_imscp" => 2021051700,
            "mod_label" => 2021051700,
            "mod_lesson" => 2021051700,
            "mod_lti" => 2021051701,
            "mod_page" => 2021051700,
            "mod_quiz" => 2021051700,
            "mod_resource" => 2021051700,
            "mod_scorm" => 2021051700,
            "mod_survey" => 2021051700,
            "mod_url" => 2021051700,
            "mod_wiki" => 2021051700,
            "mod_workshop" => 2021051700,
            "assignsubmission_comments" => 2021051700,
            "assignsubmission_file" => 2021051700,
            "assignsubmission_onlinetext" => 2021051700,
            "assignfeedback_comments" => 2021051700,
            "assignfeedback_editpdf" => 2021051701,
            "assignfeedback_file" => 2021051700,
            "assignfeedback_offline" => 2021051700,
            "assignment_offline" => 2021051700,
            "assignment_online" => 2021051700,
            "assignment_upload" => 2021051700,
            "assignment_uploadsingle" => 2021051700,
            "booktool_exportimscp" => 2021051700,
            "booktool_importhtml" => 2021051700,
            "booktool_print" => 2021051700,
            "datafield_checkbox" => 2021051700,
            "datafield_date" => 2021051700,
            "datafield_file" => 2021051700,
            "datafield_latlong" => 2021051700,
            "datafield_menu" => 2021051700,
            "datafield_multimenu" => 2021051700,
            "datafield_number" => 2021051700,
            "datafield_picture" => 2021051700,
            "datafield_radiobutton" => 2021051700,
            "datafield_text" => 2021051700,
            "datafield_textarea" => 2021051700,
            "datafield_url" => 2021051700,
            "datapreset_imagegallery" => 2021051700,
            "forumreport_summary" => 2021051700,
            "ltiservice_basicoutcomes" => 2021051700,
            "ltiservice_gradebookservices" => 2021051700,
            "ltiservice_memberships" => 2021051700,
            "ltiservice_profile" => 2021051700,
            "ltiservice_toolproxy" => 2021051700,
            "ltiservice_toolsettings" => 2021051700,
            "quiz_grading" => 2021051700,
            "quiz_overview" => 2021051700,
            "quiz_responses" => 2021051700,
            "quiz_statistics" => 2021051700,
            "quizaccess_delaybetweenattempts" => 2021051700,
            "quizaccess_ipaddress" => 2021051700,
            "quizaccess_numattempts" => 2021051700,
            "quizaccess_offlineattempts" => 2021051700,
            "quizaccess_openclosedate" => 2021051700,
            "quizaccess_password" => 2021051700,
            "quizaccess_seb" => 2021051700,
            "quizaccess_securewindow" => 2021051700,
            "quizaccess_timelimit" => 2021051700,
            "scormreport_basic" => 2021051700,
            "scormreport_graphs" => 2021051700,
            "scormreport_interactions" => 2021051700,
            "scormreport_objectives" => 2021051700,
            "workshopform_accumulative" => 2021051700,
            "workshopform_comments" => 2021051700,
            "workshopform_numerrors" => 2021051700,
            "workshopform_rubric" => 2021051700,
            "workshopallocation_manual" => 2021051700,
            "workshopallocation_random" => 2021051700,
            "workshopallocation_scheduled" => 2021051700,
            "workshopeval_best" => 2021051700,
            "block_accessreview" => 2021051700,
            "block_activity_modules" => 2021051700,
            "block_activity_results" => 2021051700,
            "block_admin_bookmarks" => 2021051700,
            "block_badges" => 2021051700,
            "block_blog_menu" => 2021051700,
            "block_blog_recent" => 2021051700,
            "block_blog_tags" => 2021051700,
            "block_calendar_month" => 2021051700,
            "block_calendar_upcoming" => 2021051700,
            "block_comments" => 2021051700,
            "block_completionstatus" => 2021051700,
            "block_course_list" => 2021051700,
            "block_course_summary" => 2021051700,
            "block_feedback" => 2021051700,
            "block_globalsearch" => 2021051700,
            "block_glossary_random" => 2021051700,
            "block_html" => 2021051700,
            "block_login" => 2021051700,
            "block_lp" => 2021051700,
            "block_mentees" => 2021051700,
            "block_mnet_hosts" => 2021051700,
            "block_myoverview" => 2021051700,
            "block_myprofile" => 2021051700,
            "block_navigation" => 2021051700,
            "block_news_items" => 2021051700,
            "block_online_users" => 2021051700,
            "block_private_files" => 2021051700,
            "block_quiz_results" => 2021051700,
            "block_recent_activity" => 2021051700,
            "block_recentlyaccessedcourses" => 2021051700,
            "block_recentlyaccesseditems" => 2021051700,
            "block_rss_client" => 2021051700,
            "block_search_forums" => 2021051700,
            "block_section_links" => 2021051700,
            "block_selfcompletion" => 2021051700,
            "block_settings" => 2021051700,
            "block_site_main_menu" => 2021051700,
            "block_social_activities" => 2021051700,
            "block_starredcourses" => 2021051700,
            "block_tag_flickr" => 2021051700,
            "block_tag_youtube" => 2021051700,
            "block_tags" => 2021051700,
            "block_timeline" => 2021051700,
            "qtype_calculated" => 2021051700,
            "qtype_calculatedmulti" => 2021051700,
            "qtype_calculatedsimple" => 2021051700,
            "qtype_ddimageortext" => 2021051700,
            "qtype_ddmarker" => 2021051700,
            "qtype_ddwtos" => 2021051700,
            "qtype_description" => 2021051700,
            "qtype_essay" => 2021051700,
            "qtype_gapselect" => 2021051700,
            "qtype_match" => 2021051700,
            "qtype_missingtype" => 2021051700,
            "qtype_multianswer" => 2021051701,
            "qtype_multichoice" => 2021051700,
            "qtype_numerical" => 2021051700,
            "qtype_random" => 2021051700,
            "qtype_randomsamatch" => 2021051700,
            "qtype_shortanswer" => 2021051700,
            "qtype_truefalse" => 2021051700,
            "qbehaviour_adaptive" => 2021051700,
            "qbehaviour_adaptivenopenalty" => 2021051700,
            "qbehaviour_deferredcbm" => 2021051700,
            "qbehaviour_deferredfeedback" => 2021051700,
            "qbehaviour_immediatecbm" => 2021051700,
            "qbehaviour_immediatefeedback" => 2021051700,
            "qbehaviour_informationitem" => 2021051700,
            "qbehaviour_interactive" => 2021051700,
            "qbehaviour_interactivecountback" => 2021051700,
            "qbehaviour_manualgraded" => 2021051700,
            "qbehaviour_missing" => 2021051700,
            "qformat_aiken" => 2021051700,
            "qformat_blackboard_six" => 2021051700,
            "qformat_examview" => 2021051700,
            "qformat_gift" => 2021051700,
            "qformat_missingword" => 2021051700,
            "qformat_multianswer" => 2021051700,
            "qformat_webct" => 2021051700,
            "qformat_xhtml" => 2021051700,
            "qformat_xml" => 2021051700,
            "filter_activitynames" => 2021051700,
            "filter_algebra" => 2021051700,
            "filter_censor" => 2021051700,
            "filter_data" => 2021051700,
            "filter_displayh5p" => 2021051700,
            "filter_emailprotect" => 2021051700,
            "filter_emoticon" => 2021051700,
            "filter_glossary" => 2021051700,
            "filter_mathjaxloader" => 2021051700,
            "filter_mediaplugin" => 2021051700,
            "filter_multilang" => 2021051700,
            "filter_tex" => 2021051700,
            "filter_tidy" => 2021051700,
            "filter_urltolink" => 2021051700,
            "editor_atto" => 2021051700,
            "editor_textarea" => 2021051700,
            "editor_tinymce" => 2021051700,
            "atto_accessibilitychecker" => 2021051700,
            "atto_accessibilityhelper" => 2021051700,
            "atto_align" => 2021051700,
            "atto_backcolor" => 2021051700,
            "atto_bold" => 2021051700,
            "atto_charmap" => 2021051700,
            "atto_clear" => 2021051700,
            "atto_collapse" => 2021051700,
            "atto_emojipicker" => 2021051700,
            "atto_emoticon" => 2021051700,
            "atto_equation" => 2021051700,
            "atto_fontcolor" => 2021051700,
            "atto_h5p" => 2021051700,
            "atto_html" => 2021051700,
            "atto_image" => 2021051700,
            "atto_indent" => 2021051700,
            "atto_italic" => 2021051700,
            "atto_link" => 2021051700,
            "atto_managefiles" => 2021051700,
            "atto_media" => 2021051700,
            "atto_noautolink" => 2021051700,
            "atto_orderedlist" => 2021051700,
            "atto_recordrtc" => 2021051700,
            "atto_rtl" => 2021051700,
            "atto_strike" => 2021051700,
            "atto_subscript" => 2021051700,
            "atto_superscript" => 2021051700,
            "atto_table" => 2021051700,
            "atto_title" => 2021051700,
            "atto_underline" => 2021051700,
            "atto_undo" => 2021051700,
            "atto_unorderedlist" => 2021051700,
            "tinymce_ctrlhelp" => 2021051700,
            "tinymce_managefiles" => 2021051700,
            "tinymce_moodleemoticon" => 2021051700,
            "tinymce_moodleimage" => 2021051700,
            "tinymce_moodlemedia" => 2021051700,
            "tinymce_moodlenolink" => 2021051700,
            "tinymce_pdw" => 2021051700,
            "tinymce_spellchecker" => 2021051700,
            "tinymce_wrap" => 2021051700,
            "enrol_category" => 2021051700,
            "enrol_cohort" => 2021051700,
            "enrol_database" => 2021051700,
            "enrol_fee" => 2021051700,
            "enrol_flatfile" => 2021051700,
            "enrol_guest" => 2021051700,
            "enrol_imsenterprise" => 2021051700,
            "enrol_ldap" => 2021051700,
            "enrol_lti" => 2021051700,
            "enrol_manual" => 2021051700,
            "enrol_meta" => 2021051700,
            "enrol_mnet" => 2021051700,
            "enrol_paypal" => 2021051700,
            "enrol_self" => 2021051700,
            "auth_cas" => 2021051700,
            "auth_db" => 2021051700,
            "auth_email" => 2021051700,
            "auth_ldap" => 2021051700,
            "auth_lti" => 2021051700,
            "auth_manual" => 2021051700,
            "auth_mnet" => 2021051700,
            "auth_nologin" => 2021051700,
            "auth_none" => 2021051700,
            "auth_oauth2" => 2021051700,
            "auth_shibboleth" => 2021051700,
            "auth_webservice" => 2021051700,
            "tool_analytics" => 2021051700,
            "tool_availabilityconditions" => 2021051700,
            "tool_behat" => 2021051700,
            "tool_brickfield" => 2021051700,
            "tool_capability" => 2021051700,
            "tool_cohortroles" => 2021051700,
            "tool_customlang" => 2021051700,
            "tool_dataprivacy" => 2021051700,
            "tool_dbtransfer" => 2021051700,
            "tool_filetypes" => 2021051700,
            "tool_generator" => 2021051700,
            "tool_health" => 2021051700,
            "tool_httpsreplace" => 2021051700,
            "tool_innodb" => 2021051700,
            "tool_installaddon" => 2021051700,
            "tool_langimport" => 2021051700,
            "tool_licensemanager" => 2021051700,
            "tool_log" => 2021051700,
            "tool_lp" => 2021051700,
            "tool_lpimportcsv" => 2021051700,
            "tool_lpmigrate" => 2021051700,
            "tool_messageinbound" => 2021051700,
            "tool_mobile" => 2021051700,
            "tool_monitor" => 2021051700,
            "tool_moodlenet" => 2021051701,
            "tool_multilangupgrade" => 2021051700,
            "tool_oauth2" => 2021051700,
            "tool_phpunit" => 2021051700,
            "tool_policy" => 2021051700,
            "tool_profiling" => 2021051700,
            "tool_recyclebin" => 2021051700,
            "tool_replace" => 2021051700,
            "tool_spamcleaner" => 2021051700,
            "tool_task" => 2021051700,
            "tool_templatelibrary" => 2021051700,
            "tool_unsuproles" => 2021051700,
            "tool_uploadcourse" => 2021051700,
            "tool_uploaduser" => 2021051700,
            "tool_usertours" => 2021051700,
            "tool_xmldb" => 2021051700,
            "logstore_database" => 2021051700,
            "logstore_legacy" => 2021051700,
            "logstore_standard" => 2021051700,
            "antivirus_clamav" => 2021051700,
            "availability_completion" => 2021051700,
            "availability_date" => 2021051700,
            "availability_grade" => 2021051700,
            "availability_group" => 2021051700,
            "availability_grouping" => 2021051700,
            "availability_profile" => 2021051700,
            "calendartype_gregorian" => 2021051700,
            "customfield_checkbox" => 2021051700,
            "customfield_date" => 2021051700,
            "customfield_select" => 2021051700,
            "customfield_text" => 2021051700,
            "customfield_textarea" => 2021051700,
            "message_airnotifier" => 2021051700,
            "message_email" => 2021051700,
            "message_jabber" => 2021051700,
            "message_popup" => 2021051700,
            "media_html5audio" => 2021051700,
            "media_html5video" => 2021051700,
            "media_swf" => 2021051700,
            "media_videojs" => 2021051700,
            "media_vimeo" => 2021051700,
            "media_youtube" => 2021051700,
            "format_singleactivity" => 2021051700,
            "format_social" => 2021051700,
            "format_topics" => 2021051700,
            "format_weeks" => 2021051700,
            "dataformat_csv" => 2021051700,
            "dataformat_excel" => 2021051700,
            "dataformat_html" => 2021051700,
            "dataformat_json" => 2021051700,
            "dataformat_ods" => 2021051700,
            "dataformat_pdf" => 2021051700,
            "profilefield_checkbox" => 2021051700,
            "profilefield_datetime" => 2021051700,
            "profilefield_menu" => 2021051700,
            "profilefield_social" => 2021051700,
            "profilefield_text" => 2021051700,
            "profilefield_textarea" => 2021051700,
            "report_backups" => 2021051700,
            "report_competency" => 2021051700,
            "report_completion" => 2021051700,
            "report_configlog" => 2021051700,
            "report_courseoverview" => 2021051700,
            "report_eventlist" => 2021051700,
            "report_infectedfiles" => 2021051700,
            "report_insights" => 2021051700,
            "report_log" => 2021051700,
            "report_loglive" => 2021051700,
            "report_outline" => 2021051700,
            "report_participation" => 2021051700,
            "report_performance" => 2021051700,
            "report_progress" => 2021051700,
            "report_questioninstances" => 2021051700,
            "report_security" => 2021051700,
            "report_stats" => 2021051700,
            "report_status" => 2021051700,
            "report_usersessions" => 2021051700,
            "gradeexport_ods" => 2021051700,
            "gradeexport_txt" => 2021051700,
            "gradeexport_xls" => 2021051700,
            "gradeexport_xml" => 2021051700,
            "gradeimport_csv" => 2021051700,
            "gradeimport_direct" => 2021051700,
            "gradeimport_xml" => 2021051700,
            "gradereport_grader" => 2021051700,
            "gradereport_history" => 2021051700,
            "gradereport_outcomes" => 2021051700,
            "gradereport_overview" => 2021051700,
            "gradereport_singleview" => 2021051700,
            "gradereport_user" => 2021051700,
            "gradingform_guide" => 2021051700,
            "gradingform_rubric" => 2021051700,
            "mlbackend_php" => 2021051700,
            "mlbackend_python" => 2021051700,
            "mnetservice_enrol" => 2021051700,
            "webservice_rest" => 2021051700,
            "webservice_soap" => 2021051700,
            "webservice_xmlrpc" => 2021051700,
            "repository_areafiles" => 2021051700,
            "repository_boxnet" => 2021051700,
            "repository_contentbank" => 2021051700,
            "repository_coursefiles" => 2021051700,
            "repository_dropbox" => 2021051700,
            "repository_equella" => 2021051700,
            "repository_filesystem" => 2021051700,
            "repository_flickr" => 2021051700,
            "repository_flickr_public" => 2021051700,
            "repository_googledocs" => 2021051700,
            "repository_local" => 2021051700,
            "repository_merlot" => 2021051700,
            "repository_nextcloud" => 2021051700,
            "repository_onedrive" => 2021051700,
            "repository_picasa" => 2021051700,
            "repository_recent" => 2021051700,
            "repository_s3" => 2021051700,
            "repository_skydrive" => 2021051700,
            "repository_upload" => 2021051700,
            "repository_url" => 2021051700,
            "repository_user" => 2021051700,
            "repository_webdav" => 2021051700,
            "repository_wikimedia" => 2021051700,
            "repository_youtube" => 2021051700,
            "portfolio_boxnet" => 2021051700,
            "portfolio_download" => 2021051700,
            "portfolio_flickr" => 2021051700,
            "portfolio_googledocs" => 2021051700,
            "portfolio_mahara" => 2021051700,
            "portfolio_picasa" => 2021051700,
            "search_simpledb" => 2021051700,
            "search_solr" => 2021051700,
            "cachestore_apcu" => 2021051700,
            "cachestore_file" => 2021051700,
            "cachestore_memcached" => 2021051700,
            "cachestore_mongodb" => 2021051700,
            "cachestore_redis" => 2021051700,
            "cachestore_session" => 2021051700,
            "cachestore_static" => 2021051700,
            "cachelock_file" => 2021051700,
            "fileconverter_googledrive" => 2021051700,
            "fileconverter_unoconv" => 2021051700,
            "contenttype_h5p" => 2021051700,
            "theme_boost" => 2021051700,
            "theme_classic" => 2021051700,
            "h5plib_v124" => 2021051700,
            "paygw_paypal" => 2021051700,
        ];
    }
}
