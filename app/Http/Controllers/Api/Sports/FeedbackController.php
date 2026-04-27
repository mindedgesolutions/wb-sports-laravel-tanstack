<?php

namespace App\Http\Controllers\Api\Sports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sports\FeedbackRequest;
use App\Mail\SportsFeedback;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function sendFeedback(FeedbackRequest $request)
    {
        try {
            DB::beginTransaction();

            $toEmail = config('lookup.services_admin_email');
            $feedbackType = $request->feedbackType;
            $fromName = trim($request->name);
            $fromMobile = trim($request->mobile);
            $fromEmail = trim($request->email);
            $fromAddress = trim($request->address);
            $subject = trim($request->subject);
            $message = trim($request->message);

            Mail::to($toEmail)->send(new SportsFeedback($feedbackType, $fromName, $fromMobile, $fromEmail, $fromAddress, $subject, $message));

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
