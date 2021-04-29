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

    function datarequest_status($requestId) {
	return $this->api->call('datarequest_get',
                                ['request_id' => $requestId])->data->requestStatus;
    }

    public function index() {
        $this->config->load('config');
        $items = $this->config->item('browser-items-per-page');

        # Get user group memberships
        $isProjectManager    = $this->api->call('datarequest_is_project_manager')->data;
        $isDatamanager       = $this->api->call('datarequest_is_datamanager')->data;
        $isExecutiveDirector = $this->api->call('datarequest_is_executive_director')->data;

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
            'isProjectManager'    => $isProjectManager,
            'isExecutiveDirector' => $isExecutiveDirector,
            'isDatamanager'       => $isDatamanager,
            'help_contact_name'   => $this->config->item('datarequest_help_contact_name'),
            'help_contact_email'  => $this->config->item('datarequest_help_contact_email')
        );

        loadView('/datarequest/index', $viewParams);
    }

    public function view($requestId) {
        # Check user group memberships and statuses
        $isProjectManager    = $this->api->call('datarequest_is_project_manager')->data;
        $isExecutiveDirector = $this->api->call('datarequest_is_executive_director')->data;
        $isDatamanager       = $this->api->call('datarequest_is_datamanager')->data;
        $isDMCMember         = $this->api->call('datarequest_is_dmc_member')->data;
        $isRequestOwner      = $this->api->call('datarequest_is_owner',
                                                ['request_id' => $requestId])->data;
        $isReviewer          = $this->api->call('datarequest_is_reviewer',
                                                ['request_id' => $requestId])->data;

        # If the user is neither of the above, return a 403
        if (!$isProjectManager && !$isExecutiveDirector && !$isDatamanager && !$isDMCMember &&
            !$isRequestOwner) {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
                     array("PRELIMINARY_RESUBMIT", "RESUBMIT_AFTER_DATAMANAGER_REVIEW", "RESUBMIT",
                           "PRELIMINARY_REJECT", "REJECTED_AFTER_DATAMANAGER_REVIEW",
                           "REJECTED"))) {
            $feedback = json_decode($this->api->call('datarequest_feedback_get',
                                    ['request_id' => $requestId])->data);
            $viewParams['feedback'] = $feedback;
        }

        loadView('datarequest/datarequest/view', $viewParams);
    }

    public function add($previousRequestId = NULL) {
        // Load CSRF token
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
        // Check permissions
        $isRequestOwner = $this->api->call('datarequest_is_owner',
                                           ['request_id' => $draftRequestId])->data;
        $requestStatus  = $this->datarequest_status($draftRequestId);
        if (!$isRequestOwner or $requestStatus !== "DRAFT") {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isRequestOwner = $this->api->call('datarequest_is_owner',
                                           ['request_id' => $requestId])->data;
        $requestStatus  = $this->datarequest_status($requestId);
        if (!$isRequestOwner or $requestStatus !== "PENDING_ATTACHMENTS") {
            $this->output->set_status_header('403');
            return;
        }

        // Get current attachments
        $attachments = $this->api->call('datarequest_attachments_get',
                                        ['request_id' => $requestId])->data;

        // Load CSRF token
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
        // Check permissions
        $isRequestOwner = $this->api->call('datarequest_is_owner',
                                           ['request_id' => $requestId])->data;
        $requestStatus  = $this->datarequest_status($requestId);
        if (!$isRequestOwner or $requestStatus !== "PENDING_ATTACHMENTS") {
            $this->output->set_status_header('403');
            return;
        }

        # Load Filesystem model and PathLibrary library
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');

        # Construct path to data request directory (in which the document will be stored)
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/attachments/';
        $rodsaccount = $this->rodsuser->getRodsAccount();

        # Upload the document
        $this->api->call('datarequest_attachment_upload_permission', ['request_id' => $requestId,
                                                                      'action' => 'grant'])->data;
        $this->filesystem->upload($rodsaccount, $filePath, $_FILES["file"]);
        $this->api->call('datarequest_attachment_post_upload_actions',
                         ['request_id' => $requestId, 'filename' => $_FILES["file"]["name"]]);
        $this->api->call('datarequest_attachment_upload_permission', ['request_id' => $requestId,
                                                                      'action' => 'grantread'])->data;
    }

    public function submit_attachments($requestId) {
        // Check permissions
        $isRequestOwner = $this->api->call('datarequest_is_owner',
                                           ['request_id' => $requestId])->data;
        $requestStatus  = $this->datarequest_status($requestId);
        if (!$isRequestOwner or $requestStatus !== "PENDING_ATTACHMENTS") {
            $this->output->set_status_header('403');
            return;
        }

        // Submit attachments
	$result = $this->api->call('datarequest_attachments_submit',
                                   ['request_id' => $requestId]);

        // Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }

    protected function get_attachments($requestId) {
        return $this->api->call('datarequest_attachments_get', ['request_id' => $requestId])->data;
    }

    public function download_attachment($requestId) {
        // Check permissions
        $isProjectManager    = $this->api->call('datarequest_is_project_manager')->data;
        $isExecutiveDirector = $this->api->call('datarequest_is_executive_director')->data;
        $isDatamanager       = $this->api->call('datarequest_is_datamanager')->data;
        $isDMCMember         = $this->api->call('datarequest_is_dmc_member')->data;
        $isRequestOwner      = $this->api->call('datarequest_is_owner',
                                                ['request_id' => $requestId])->data;
        if (!$isProjectManager && !$isExecutiveDirector && !$isDatamanager && !$isDMCMember &&
            !$isRequestOwner) {
            $this->output->set_status_header('403');
            return;
        }

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
        // Check permissions
        $isProjectManager = $this->api->call('datarequest_is_project_manager')->data;
        $requestStatus = $this->datarequest_status($requestId);
        if (!$isProjectManager or $requestStatus !== "SUBMITTED") {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isDatamanager = $this->api->call('datarequest_is_datamanager')->data;
        $requestStatus = $this->datarequest_status($requestId);
        if (!$isDatamanager or !in_array($requestStatus, ["PRELIMINARY_ACCEPT",
                                                          "PRELIMINARY_REJECT",
                                                          "PRELIMINARY_RESUBMIT"])) {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isProjectManager = $this->api->call('datarequest_is_project_manager')->data;
        $requestStatus = $this->datarequest_status($requestId);
        if (!$isProjectManager or !in_array($requestStatus, ["DATAMANAGER_ACCEPT",
                                                             "DATAMANAGER_REJECT",
                                                             "DATAMANAGER_RESUBMIT"])) {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isExecutiveDirector = $this->api->call('datarequest_is_executive_director')->data;
        $requestStatus       = $this->datarequest_status($requestId);
        if (!$isExecutiveDirector or $requestStatus !== "DATAMANAGER_REVIEW_ACCEPTED") {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isProjectManager = $this->api->call('datarequest_is_project_manager')->data;
        $requestStatus    = $this->datarequest_status($requestId);
        if (!$isProjectManager or $requestStatus !== "CONTRIBUTION_ACCEPTED") {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isReviewer    = $this->api->call('datarequest_is_reviewer',
                                          ['request_id' => $requestId])->data;
        $requestStatus = $this->datarequest_status($requestId);
        if (!$isReviewer or $requestStatus !== "UNDER_REVIEW") {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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
        // Check permissions
        $isProjectManager = $this->api->call('datarequest_is_project_manager')->data;
        $requestStatus    = $this->datarequest_status($requestId);
        if (!$isProjectManager or !in_array($requestStatus, ["DAO_SUBMITTED", "REVIEWED"])) {
            $this->output->set_status_header('403');
            return;
        }

        // Load CSRF token
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

        if ($requestStatus === "DAO_SUBMITTED") {
            loadView('/datarequest/dao_evaluate', $viewParams);
        } else {
            loadView('/datarequest/evaluate', $viewParams);
        }
    }

    public function contribution_confirm($requestId) {
        // Check permissions
        $isExecutiveDirector = $this->api->call('datarequest_is_executive_director')->data;
        $requestStatus       = $this->datarequest_status($requestId);
        if (!$isExecutiveDirector or $requestStatus !== "APPROVED") {
            $this->output->set_status_header('403');
            return;
        }

        // Set status to CONTRIBUTION_CONFIRMED
	$result = $this->api->call('datarequest_contribution_confirm',
                                   ['request_id' => $requestId]);

        // Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }

    public function upload_dta($requestId) {
        // Check permissions
        $isDatamanager = $this->api->call('datarequest_is_datamanager')->data;
        $requestStatus = $this->datarequest_status($requestId);
        if (!$isDatamanager or !in_array($requestStatus, ["CONTRIBUTION_CONFIRMED",
                                                          "DAO_APPROVED"])) {
            $this->output->set_status_header('403');
            return;
        }

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
        // Check permissions
        $isRequestOwner = $this->api->call('datarequest_is_owner',
                                           ['request_id' => $requestId])->data;
        if (!$isRequestOwner) {
            $this->output->set_status_header('403');
            return;
        }

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
        // Check permissions
        $isRequestOwner = $this->api->call('datarequest_is_owner',
                                           ['request_id' => $requestId])->data;
        $requestStatus  = $this->datarequest_status($requestId);
        if (!$isRequestOwner or $requestStatus !== "DTA_READY") {
            $this->output->set_status_header('403');
        }

        # Load Filesystem model and PathLibrary library
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');

        # Construct path to data request directory (in which the document will
        # be stored)
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/signed_dta/';
        $rodsaccount = $this->rodsuser->getRodsAccount();

        # Upload the document
        $this->api->call('datarequest_dta_upload_permission', ['request_id' => $requestId,
                                                               'action' => 'grant'])->data;
        $this->filesystem->upload($rodsaccount, $filePath, $_FILES["file"]);
        $this->api->call('datarequest_signed_dta_post_upload_actions',
                         ['request_id' => $requestId, 'filename' => $_FILES["file"]["name"]]);
        $this->api->call('datarequest_dta_upload_permission', ['request_id' => $requestId,
                                                               'action' => 'revoke'])->data;
    }

    public function download_signed_dta($requestId) {
        // Check permissions
        $isProjectManager = $this->api->call('datarequest_is_project_manager')->data;
        $isDatamanager    = $this->api->call('datarequest_is_datamanager')->data;
        $requestStatus    = $this->datarequest_status($requestId);
        if ((!$isDatamanager && !$isProjectManager) or !in_array($requestStatus,
                                                                 ["DTA_SIGNED", "DATA_READY"])) {
            $this->output->set_status_header('403');
        }

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
        // Check permissions
        $isDatamanager = $this->api->call('datarequest_is_datamanager')->data;
        $requestStatus = $this->datarequest_status($requestId);
        if (!$isDatamanager or $requestStatus !== "DTA_SIGNED") {
            $this->output->set_status_header('403');
            return;
        }

        // Set status to data_ready
	$result = $this->api->call('datarequest_data_ready',
                                   ['request_id' => $requestId]);

        // Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }
}
