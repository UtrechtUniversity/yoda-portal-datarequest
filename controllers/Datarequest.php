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

    protected function human_readable_status($status) {
        switch($status) {
            case "DRAFT":
                return "In draft";
            case "PENDING_ATTACHMENTS":
                return "Pending attachments";
            case "DAO_SUBMITTED":
                return "Submitted (data assessment)";
            case "SUBMITTED":
                return "Submitted";
            case "PRELIMINARY_ACCEPT":
                return "Preliminary accept";
            case "PRELIMINARY_REJECT":
                return "Rejected at preliminary review";
            case "PRELIMINARY_RESUBMIT":
                return "Rejected (resubmit) at preliminary review";
            case "DATAMANAGER_ACCEPT":
                return "Datamanager accept";
            case "DATAMANAGER_REJECT":
                return "Datamanager reject";
            case "DATAMANAGER_RESUBMIT":
                return "Datamanager reject (resubmit)";
            case "UNDER_REVIEW":
                return "Under review";
            case "REJECTED_AFTER_DATAMANAGER_REVIEW":
                return "Rejected after datamanager review";
            case "RESUBMIT_AFTER_DATAMANAGER_REVIEW":
                return "Rejected (resubmit) after datamanager review";
            case "REVIEWED":
                return "Reviewed";
            case "APPROVED":
                return "Approved";
            case "REJECTED":
                return "Rejected";
            case "RESUBMIT":
                return "Rejected (resubmit)";
            case "RESUBMITTED":
                return "Resubmitted";
            case "DAO_APPROVED":
                return "Approved (data assessment)";
            case "PREREGISTRATION_SUBMITTED":
                return "Preregistration submitted";
            case "PREREGISTRATION_CONFIRMED":
                return "Preregistration confirmed";
            case "DTA_READY":
                return "DTA ready";
            case "DTA_SIGNED":
                return "DTA signed";
            case "DATA_READY":
                return "Data ready";
        }
    }

    protected function datarequest_info($requestId) {
        $datarequest        = $this->api->call('datarequest_get', ['request_id' => $requestId])->data;
        $availableDocuments = $datarequest->requestAvailableDocuments;
        $status             = $datarequest->requestStatus;
        $humanStatus        = $this->human_readable_status($status);
        $type               = $datarequest->requestType;
        $request            = $datarequest->requestJSON;

        return ['type' => $type, 'status' => $status, 'humanStatus' => $humanStatus,
                'request' => $request, 'availableDocuments' => $availableDocuments];
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

    public function archive() {
        $this->index($archived = True);
    }

    public function dacrequests() {
        $this->index($archived = False, $dacrequests = True);
    }

    public function index($archived = False, $dacrequests = False) {
        $this->config->load('config');
        $items = $this->config->item('browser-items-per-page');

        # Check if user is allowed to submit data request
        $roles             = $this->api->call('datarequest_roles_get')->data;
        $submissionAllowed = (!in_array("PM", $roles) and !in_array("DM", $roles));
        $isDACMember       = in_array("DAC", $roles);

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
            'archived'            => $archived,
            'dacrequests'         => $dacrequests,
            'activeModule'        => 'datarequest',
            'submissionAllowed'   => $submissionAllowed,
            'isDACMember'         => $isDACMember,
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
        $isDatamanager       = in_array("DM", $roles);
        $isDACMember         = in_array("DAC", $roles);
        $isRequestOwner      = in_array("OWN", $roles);
        $isReviewer          = in_array("REV", $roles);
        $isPendingReviewer   = in_array("PENREV", $roles);

        # If the user is neither of the above, return a 403
        if (!$isProjectManager && !$isDatamanager && !$isDACMember && !$isRequestOwner) {
            $this->output->set_status_header('403');
            return;
        }

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

	# Get datarequest status
        $requestInfo        = $this->datarequest_info($requestId);
        $requestStatus      = $requestInfo['status'];
        $availableDocuments = $requestInfo['availableDocuments'];
        $humanRequestStatus = $requestInfo['humanStatus'];
        $requestType        = $requestInfo['type'];
        $request            = json_decode($requestInfo['request'], true);

        # Set view params and render the view
        $viewParams = array(
            'tokenName'           => $tokenName,
            'tokenHash'           => $tokenHash,
            'requestId'           => $requestId,
            'requestType'         => $requestType,
            'requestStatus'       => $requestStatus,
            'availableDocuments'  => json_encode($availableDocuments),
            'humanRequestStatus'  => $humanRequestStatus,
            'request'             => $request,
            'isReviewer'          => $isReviewer,
            'isPendingReviewer'   => $isPendingReviewer,
            'isProjectManager'    => $isProjectManager,
            'isDatamanager'       => $isDatamanager,
            'isRequestOwner'      => $isRequestOwner,
            'attachments'         => $this->get_attachments($requestId),
            'activeModule'        => 'datarequest',
            'scriptIncludes'      => array(
                'js/datarequest/view.js'
            ),
            'styleIncludes'       => array(
                'css/datarequest/forms.css',
                'css/datarequest/view.css'
            )
        );

        # Get ID of resubmitted in case the data request has been resubmitted
        if ($requestStatus == "RESUBMITTED") {
            $resubmissionId = $this->api->call('datarequest_resubmission_id_get',
                                               ['request_id' => $requestId])->data;
            $viewParams['resubmissionId'] = $resubmissionId;
        }

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
        if (!$this->permission_check($requestId, ["PM", "ED", "DM", "DAC", "OWN"], null)) { return; }

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

    public function assign($requestId) {
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

        if ($this->datarequest_info($requestId)['status'] === "DAO_SUBMITTED") {
            loadView('/datarequest/dao_evaluate', $viewParams);
        } else {
            loadView('/datarequest/evaluate', $viewParams);
        }
    }

    public function preregister($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["OWN"], ["APPROVED"])) { return; }

        # Get approval conditions (if any)
        $approvalConditions= json_decode($this->api->call('datarequest_approval_conditions_get',
                                         ['request_id' => $requestId])->data);

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'          => $tokenName,
            'tokenHash'          => $tokenHash,
            'activeModule'       => 'datarequest',
            'requestId'          => $requestId,
            'approvalConditions' => $approvalConditions, 
            'styleIncludes'      => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/preregister', $viewParams);
    }

    public function preregistration_confirm($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM"], ["PREREGISTRATION_SUBMITTED"])) { return; }

        # Get OSF preregistration URL
        $osfUrl= json_decode($this->api->call('datarequest_preregistration_get',
                                              ['request_id' => $requestId])->data)->preregistration_url;

        # Load CSRF token
        $tokenName = $this->security->get_csrf_token_name();
        $tokenHash = $this->security->get_csrf_hash();

        $viewParams = array(
            'tokenName'     => $tokenName,
            'tokenHash'     => $tokenHash,
            'activeModule'  => 'datarequest',
            'requestId'     => $requestId,
            'attachments'   => $this->get_attachments($requestId),
            'osfUrl'       => $osfUrl,
            'styleIncludes' => array(
                'css/datarequest/forms.css'
            )
        );

        loadView('/datarequest/preregistration_confirm', $viewParams);
    }

    public function confirm_preregistration($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["PM"], ["PREREGISTRATION_SUBMITTED"])) { return; }

        # Set status to PREREGISTRATION_CONFIRMED
       $result = $this->api->call('datarequest_preregistration_confirm',
                                   ['request_id' => $requestId]);

        # Redirect to view/
        if ($result->status === "ok") {
            redirect('/datarequest/view/' . $requestId);
        }
    }

    public function upload_dta($requestId) {
        # Check permissions
        if (!$this->permission_check($requestId, ["DM"], ["PREREGISTRATION_CONFIRMED",
                                                          "DAO_APPROVED"])) { return; }

        # Load Filesystem model and PathLibrary library
        $this->load->model('filesystem');
        $this->load->library('pathlibrary');

        # Construct path to data request directory (in which the document will be stored)
        $pathStart   = $this->pathlibrary->getPathStart($this->config);
        $filePath    = $pathStart . '/datarequests-research/' . $requestId . '/dta/';
        $rodsaccount = $this->rodsuser->getRodsAccount();

        # Verify that uploaded file is a PDF
        if (mime_content_type($_FILES["file"]['tmp_name']) != "application/pdf") {
            http_response_code(400);
            return;
        }

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
        $this->filesystem->download($rodsaccount, $file_path, "application/pdf");
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

        # Verify that uploaded file is a PDF
        if (mime_content_type($_FILES["file"]['tmp_name']) != "application/pdf") {
            http_response_code(400);
            return;
        }

        # Upload the document
        $this->api->call('datarequest_signed_dta_upload_permission', ['request_id' => $requestId,
                                                                      'action' => 'grant'])->data;
        $this->filesystem->upload($rodsaccount, $filePath, $_FILES["file"]);
        $this->api->call('datarequest_signed_dta_post_upload_actions',
                         ['request_id' => $requestId, 'filename' => $_FILES["file"]["name"]]);
        $this->api->call('datarequest_signed_dta_upload_permission', ['request_id' => $requestId,
                                                                      'action' => 'grantread'])->data;
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
        $this->filesystem->download($rodsaccount, $file_path, "application/pdf");
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
