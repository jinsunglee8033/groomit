<?php

namespace App\Model;

class Constants
{

    ### appointment status list ###
    public static $appointment_status = [
        'N' => 'Groomer Not Assigned Yet',
        'D' => 'Groomer Assigned',
        'O' => 'Groomer On The Way',
        'W' => 'Work In Progress',
        'C' => 'Cancelled',
        'S' => 'Work Completed',
        'F' => 'Payment Failure',
        'R' => 'Failed to hold amount. Please retry after updating customer credit card.',
        'L' => 'Cancelled & Rescheduled',
        'P' => 'Payment Completed'//,
        //'Z' => 'Courtesy Call Completed'
    ];

    ### Message ###
    public static $message_send_method = [
        'P' => 'Push Notification',
        'S' => 'SMS',
        'B' => 'Both'
    ];

    public static $message_sender_type = [
        'U' => 'User',
        'G' => 'Groomer',
        'A' => 'Admin'
    ];

    public static $message_receiver_type = [
        'B' => 'Groomer',
        'A' => 'User',
        'C' => 'Admin User',
        //'D' => 'All End Users',
        //'E' => 'All Groomers',
        //'F' => 'All Admin Users'
    ];

    public static $message_receiver_modal_type = [
        'A' => 'User',
        'B' => 'Groomer',
        'C' => 'Admin User',

        'D' => 'All Groomers',
//        'L' => 'Groomers Level 1',
//        'N' => 'Groomers Level 2',
//        'M' => 'Groomers Level 3',
//
//        'O' => 'NJ Groomers Only',
//        'P' => 'NY Groomers Only',

        'R' => 'Specific Groomer Group'
    ];

    public static $message_type = [
        'A' => 'Advertisement',
        'N' => 'Notification',
        'M' => 'Personal Message',
        'C' => 'Appointment Cancelled',
        'D' => 'Groomer Assigned',
        'O' => 'Groomer On The Way',
        'W' => 'Appointment Work In Progress',
        'S' => 'Appointment Work Completed',
        'F' => 'Appointment Payment Failure',
        'P' => 'Appointment Payment Completed',
        'Z' => 'Appointment Courtesy Call Completed',
        'T' => 'Tip Notification',
        'R' => 'SMS Reply',
        'RM' => 'Reminder',
        'NO' =>'Groomer Not On The Way',
        'UC' =>'User Changed Date Time'
    ];

    public static $message_app = [
        'Reminder4weeks' => 'Your last Groomit Session for PET_NAME was 4 weeks ago. To schedule, please go to our Groomit App or reply here', //(PUSH / TEXT) Auto
        'NoGroomer' => 'Thank you for your patience – unfortunately we don’t have a Groomer available for your selected time window. Please open Groomit App and reschedule or reply here.', // (PUSH / TEXT) Manual
        'Confirmation' => 'Your Groomit appointment is confirmed with GROOMER_NAME on DATETIME at ADDRESS.', //(PUSH / TEXT / EMAIL) Auto
        'ReminderTomorrow' => 'You have an upcoming Groomit Appointment tomorrow (DATE_TIME) at ADDRESS with Groomer GROOMER_NAME.', //(PUSH / Text) Auto
        'ReminderToday' => 'You have an upcoming Groomit Appointment today (DATE_TIME) at ADDRESS with Groomer GROOMER_NAME.', //(PUSH / Text) Auto
        'GroomerOnWay' => 'Groomit appointment reminder: Our groomer GROOMER_NAME is on the way. Your appointment will begin at SERVICE_TIME. Please have towels and a clean surface (table/counter top) area ready for grooming. Thank you! https://bit.ly/groomit-safe ', // (PUSH / TEXT) At Launch : Automatic, but Groomer’s APP ready => by Groomers.
        //'GroomerOnWay' => 'Our Groomer GROOMER_NAME is on the way; GROOMER_NAME arrives between ARRIVAL_TIME_RANGE. For questions reply here : before/after 15 minutes.', // (PUSH / TEXT) At Launch : Automatic, but Groomer’s APP ready => by Groomers.
        'Delay' => 'Our Groomer is running 30 minutes late.',
        'Location' => 'Our Groomer can’t find your service address. Please call GROOMER_PHONE or reply here  ', //Manual
        'ThankYou' => 'Thank you for using Groomit. Please rate your latest grooming experience within the Groomit app.', // PUSH/TEXT, Status is changed as ‘Completed’ by calls from groomers.
        'Inconvenience10' => 'Sorry for the inconvenience. Here is a $10 coupon towards your next Groomit service.', //Manual
        'Inconvenience20' => 'Sorry for the inconvenience. Here is a $20 Coupon towards your next Groomit Service.' //Manual
    ];

    public static $message_groomer = [
        'NewAppointmentRequest' => 'Groomit Appointment request DATETIME at ADDRESS. Open App to confirm or reply here.', //App PUSH / TEXT
        'AppointmentConfirmation' => 'Groomit Appointment confirmation DATETIME at ADDRESS.', //  (Auto) TEXT at launch / later with App PUSH / TEXT
        'Reminder1hBefore' => 'Your Groomit Appointment is confirmed for DATETIME with our Groomer GROOMER_NAME. Open Groomit App for details or reply here.', // (TEXT).
        'ReminderDay' => 'You have 3 upcoming Groomit Appointments today. Please prepare your tools.',
        'Schedule' => ' Please update your weekly schedule within the app.', // Reminder
        'Cancelled' => 'Your appointment for DATETIME at ADDRESS has been cancelled',
        'GroomerAssigned' => 'You are assigned to new appointments for DATETIME at ADDRESS.'

    ];

//Groomer Email:
//
//Booking Confirmation with Date / Time / Address /Package / Add-on / Dog Name
//Reminder 24h before of upcoming appointments Date / Time / Address /Package / Add-on / Dog Name

    public static $card_type = [
        'V' => 'Visa',
        'A' => 'American Express',
        'M' => 'Master',
        'D' => 'Discover'
    ];
}