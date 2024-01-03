<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        // Define response with default value
        $response = null;

        // Move Assignment out of if condition
        $user_id = $request->get('user_id');
        if (!empty($user_id)) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif ($request->__authenticatedUser->user_type === env('ADMIN_ROLE_ID') // Should use === instead of ==
            || $request->__authenticatedUser->user_type === env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = array_except($request->all(), ['_token', 'submit']); // move this out of updateJob call

        $user = $request->__authenticatedUser; // Rename variable

        $response = $this->repository->updateJob($id, $data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        // Removed unused variable
        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        // Move Assignment out of if condition
        $user_id = $request->get('user_id');
        if (!empty($user_id)) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        return null;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        // Removed unused variable
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        // Move it to top, so return early if condition true
        if ($data['flagged'] === true) { // use ===
            if ($data['admincomment'] === '') { // use ===
                return "Please, add comment";
            }
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        // simplify if to ternary and use ===/!==
        $distance = isset($data['distance']) && $data['distance'] !== "" ? $data['distance'] : "";
        $time = isset($data['time']) && $data['time'] !== "" ? $data['time'] : "";
        $job_id = isset($data['jobid']) && $data['jobid'] !== "" ? $data['jobid'] : "";
        $session = isset($data['session_time']) && $data['session_time'] !== "" ? $data['session_time'] : "";
        $manually_handled = $data['manually_handled'] === true ? 'yes' : 'no';
        $by_admin = $data['by_admin'] === true ? 'yes' : 'no';
        $admin_comment = isset($data['admincomment']) && $data['admincomment'] !== "" ? $data['admincomment'] : "";

        if ($time || $distance) {
            $affectedRows = Distance::where('job_id', '=', $job_id)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admin_comment || $session || $flagged || $manually_handled || $by_admin) {
            $affectedRows1 = Job::where('id', '=', $job_id)->update(array('admin_comments' => $admin_comment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        }

        $response = $affectedRows || $affectedRows1 ? 'Record updated!' : 'No change'; // return appropriate response

        return response($response);
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        // Removed unused call

        try {
            // check first if $job exist
            if ($job) {
                $this->repository->sendSMSNotificationToTranslator($job);
                return response(['success' => 'SMS sent']);
            } else {
                return response(['failed' => 'Job not found']);
            }
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()]); // change key to error
        }
    }

}
