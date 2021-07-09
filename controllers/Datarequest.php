<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Datarequest controller
 *
 * @package    Yoda
 * @copyright  Copyright (c) 2019, Utrecht University. All rights reserved.
 * @license    GPLv3, see LICENSE.
 */

class Datarequest extends MY_Controller
{
    public function __construct() {
        parent::__construct();

        $this->load->library('api');
    }

    protected function datarequest_status($requestId) {
	return $this->api->call('datarequest_get',
                                ['request_id' => $requestId])->data->requestStatus;
    }

    protected function permission_check($requestId, $roles, $statuses) {
        if ($this->api->call('datarequest_action_permitted', ["request_id" => $requestId,
                                                              "statuses" => $statuses,
                                                              "roles" => $roles])->data) {
            return True;
        } else {
            $this->output->set_status_header('403');
            return False;
        }
    }

    public function index() {
        $this->config->load('config');
        $items = $this->config->item('browser-items-per-page');

        # Check if user is allowed to submit data request
        $roles             = $this->api->call('datarequest_roles_get')->data;
        $submissionAllowed = !in_array("PM", $roles) and !in_array("DM", $roles) and
                             !in_array("ED", $roles);

        $viewParams = array(
            'styleIncludes'       => array(
                'lib/datatables/css/datatables.min.css',
                'lib/font-awesome/css/font-awesome.css',
                'css/datarequest/index.css'
            ),
            'scriptIncludes'      => array(
                'lib/datatables/js/datatables.min.js',
                'js/datarequest/index.js',
            ),
            'items'               => $items,
            'activeModule'        => 'datarequest',
            'submissionAllowed'   => $submissionAllowed,
            'help_contact_name'   => $this->config->item('datarequest_help_contact_name'),
            'help_contact_email'  => $this->config->item('datarequest_help_contact_email')
        );

        loadView('/datarequest/index', $viewParams);
    }

    public function view($requestId) {
        # Check user group memberships and statuses
        $roles               = $this->api->call('datarequest_roles_get',
                                                ["request_id" => $requestId])->data;
        $isProjectManager    = in_array("PM", $roles);
        $isExecutiveDirector = in_array("ED", $roles);
        $isDatamanager       = in_array("DM", $roles);
        $isDMCMember         = in_array("DMC", $roles);
        $isRequestOwner      = in_array("OWN", $roles);
        $isReviewer          = in_array("REV", $roles);

        # If the user is neither of the above, return a 403
        if (!$isProjectManager && !$isExecutiveDirector && !$isDatamanager && !$isDMCMember &&
            !$isRequestOwner) {
            $this->output->set_status_header('403');
            return;
        }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

	# Get datarequest status
        $requestStatus = $this->datarequest_status($requestId);

        # Set view params and render the view
        $viewParams = array(
            'tokenName'           => $tokenName,
            'tokenHash'           => $tokenHash,
            'requestId'           => $requestId,
            'requestStatus'       => $requestStatus,
            'isReviewer'          => $isReviewer,
            'isProjectManager'    => $isProjectManager,
            'isExecutiveDirector' => $isExecutiveDirector,
            'isDatamanager'       => $isDatamanager,
            'isRequestOwner'      => $isRequestOwner,
            'activeModule'        => 'datarequest',
            'scriptIncludes'      => array(
                'js/datarequest/view.js'
            ),
            'styleIncludes'       => array(
                'css/datarequest/view.css'
            )
        );

        # Add feedback for researcher as view param if applicable
        if (in_array($requestStatus,
                     array("PRELIMINARY_RESUBMIT", "RESUBMIT_AFTER_DATAMANAGER_REVIEW",
                           "CONTRIBUTION_RESUBMIT", "RESUBMIT", "PRELIMINARY_REJECT",
                           "REJECTED_AFTER_DATAMANAGER_REVIEW", "CONTRIBUTION_REJECTED",
                           "REJECTED"))) {
            $feedback = json_decode($this->api->call('datarequest_feedback_get',
                                    ['request_id' => $requestId])->data);
            $viewParams['feedback'] = $feedback;
        }

        loadView('datarequest/datarequest/view', $viewParams);
    }

    public function add($previousRequestId = NULL) {
        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'    => $tokenName,
            'tokenHash'    => $tokenHash,
            'activeModule' => 'datarequest'
        );
        if ($previousRequestId) {
            $viewParams['previousRequestId'] = $previousRequestId;
        }

        loadView('/datarequest/add', $viewParams);
    }

    public function add_from_draft($draftRequestId) {
        # Check permissions
        if (!$this->permission_check($draftRequestId, ["OWN"], null)) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'      => $tokenName,
            'tokenHash'      => $tokenHash,
            'draftRequestId' => $draftRequestId,
            'activeModule'   => 'datarequest'
        );

        loadView('/datarequest/add', $viewParams);
    }

    public function add_attachments($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["OWN"], ["PENDING_ATTACHMENTS"])) { return; }

        # Get current attachments
        $attachments = $this->api->call('datarequest_attachments_get',
                                        ['request_id' => $requestId])->data;

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'    => $tokenName,
            'tokenHash'    => $tokenHash,
            'requestId'    => $requestId,
            'activeModule' => 'datarequest',
            'attachments'  => $attachments
        );

        loadView('/datarequest/add_attachments', $viewParams);
    }

    public function upload_attachment($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["OWN"], ["PENDING_ATTACHMENTS"])) { return; }

        # Load Filesystem model and PathLibrary library
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');

        # Construct path to data request directory (in which the document will be stored)
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/attachments/';
        $rodsaccount = $this->rodsuser->getRodsAccount();

        # Upload the document
        $this->api->call('datarequest_attachment_upload_permission',
                         ['request_id' => $requestId, 'action' => 'grant'])->data;
        $this->filesystem->upload($rodsaccount, $filePath, $_FILES["file"]);
        $this->api->call('datarequest_attachment_post_upload_actions',
                         ['request_id' => $requestId, 'filename' => $_FILES["file"]["name"]]);
        $this->api->call('datarequest_attachment_upload_permission',
                         ['request_id' => $requestId, 'action' => 'grantread'])->data;
    }

    public function submit_attachments($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["OWN"], ["PENDING_ATTACHMENTS"])) { return; }

        # Submit attachments
	$result = $this->api->call('datarequest_attachments_submit', ['request_id' => $requestId]);

        # Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }

    protected function get_attachments($requestId) {
        return $this->api->call('datarequest_attachments_get', ['request_id' => $requestId])->data;
    }

    public function download_attachment($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM", "ED", "DM", "DMC", "OWN"], null)) { return; }

        # Get file path
        $this->load->library('pathlibrary');
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/attachments/';
        $file_path   = $filePath . $this->api->call('datarequest_attachments_get',
                                                    ['request_id' => $requestId])->data[$_GET['file']];

        # Get file
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');
        $rodsaccount = $this->rodsuser->getRodsAccount();

        return $this->filesystem->download($rodsaccount, $file_path);
    }

    public function preliminary_review($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM"], ["SUBMITTED"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/preliminaryreview', $viewParams);
    }

    public function datamanager_review($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["DM"], ["PRELIMINARY_ACCEPT"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/datamanagerreview', $viewParams);
    }

    public function dmr_review($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM"], ["DATAMANAGER_ACCEPT",
                                                          "DATAMANAGER_REJECT",
                                                          "DATAMANAGER_RESUBMIT"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/dmr_review', $viewParams);
    }

    public function contribution_review($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["ED"],
                                     ["DATAMANAGER_REVIEW_ACCEPTED"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/contribution_review', $viewParams);
    }

    public function assign($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM"], ["CONTRIBUTION_ACCEPTED"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/assign', $viewParams);
    }

    public function review($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["REV"], ["UNDER_REVIEW"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'username'      => $this->rodsuser->getUserInfo()['name'],
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/review', $viewParams);
    }

    public function evaluate($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM"], ["DAO_SUBMITTED", "REVIEWED"])) { return; }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        if ($this->datarequest_status($requestId) === "DAO_SUBMITTED") {
            loadView('/datarequest/dao_evaluate', $viewParams);
        } else {
            loadView('/datarequest/evaluate', $viewParams);
        }
    }

    public function contribution_confirm($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["ED"], ["APPROVED"])) { return; }

        # Set status to CONTRIBUTION_CONFIRMED
	$result = $this->api->call('datarequest_contribution_confirm',
                                   ['request_id' => $requestId]);

        # Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }

    public function upload_dta($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["DM"], ["CONTRIBUTION_CONFIRMED",
                                                          "DAO_APPROVED"])) { return; }

        # Load Filesystem model and PathLibrary library
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');

        # Construct path to data request directory (in which the document will be stored)
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/dta/';
        $rodsaccount = $this->rodsuser->getRodsAccount();

        # Upload the document
        $this->api->call('datarequest_dta_upload_permission', ['request_id' => $requestId,
                                                               'action' => 'grant'])->data;
        $this->filesystem->upload($rodsaccount, $filePath, $_FILES["file"]);
        $this->api->call('datarequest_dta_post_upload_actions',
                         ['request_id' => $requestId, 'filename' => $_FILES["file"]["name"]]);
        $this->api->call('datarequest_dta_upload_permission', ['request_id' => $requestId,
                                                               'action' => 'revoke'])->data;
    }

    public function download_dta($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM", "DM", "OWN"], null)) { return; }

        # Get file path
        $file_path = $this->api->call('datarequest_dta_path_get',
                                      ['request_id' => $requestId])->data;

        # Get file
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');
        $rodsaccount = $this->rodsuser->getRodsAccount();
        $this->filesystem->download($rodsaccount, $file_path);
    }

    public function upload_signed_dta($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["OWN"], ["DTA_READY"])) { return; }

        # Load Filesystem model and PathLibrary library
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');

        # Construct path to data request directory (in which the document will
        # be stored)
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/signed_dta/';
        $rodsaccount = $this->rodsuser->getRodsAccount();

        # Upload the document
        $this->api->call('datarequest_signed_dta_upload_permission', ['request_id' => $requestId,
                                                               'action' => 'grant'])->data;
        $this->filesystem->upload($rodsaccount, $filePath, $_FILES["file"]);
        $this->api->call('datarequest_signed_dta_post_upload_actions',
                         ['request_id' => $requestId, 'filename' => $_FILES["file"]["name"]]);
        $this->api->call('datarequest_signed_dta_upload_permission', ['request_id' => $requestId,
                                                               'action' => 'revoke'])->data;
    }

    public function download_signed_dta($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM", "DM", "OWN"], null)) { return; }

        # Get file path
        $file_path = $this->api->call('datarequest_signed_dta_path_get',
                                      ['request_id' => $requestId])->data;

        # Get file
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');
        $rodsaccount = $this->rodsuser->getRodsAccount();
        $this->filesystem->download($rodsaccount, $file_path);
    }

    public function data_ready($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["DM"], ["DTA_SIGNED"])) { return; }

        # Set status to data_ready
	$result = $this->api->call('datarequest_data_ready',
                                   ['request_id' => $requestId]);

        # Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }
}
