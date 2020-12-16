@extends('user.layout.default')

@section('content')
<link href="/desktop/css/login.css" rel="stylesheet">



@if ($errors->has('exception'))
  @if( !strpos($errors->first('exception'), "can't find your appointment") )
      <div class="alert alert-warning text-center">
        {{ $errors->first('exception') }}
      </div>
  @endif
@endif

<main id="main">
  <!-- There is an appt to rate -->
  <section id="appt_rates" class="survey mt-0 mt-5 pt-5">
    <form id="frm" action="/user/survey" method="post">
      {{ csrf_field() }}
        <div class="container">
          <div class="row">
            <h1 class="text-center main__title main__title--neutra mb-2">Rate your appointment</h1>
            <p class="text-center mb-5 pb-3 survey__subtitle">{{ isset($service_date) ? $service_date : ''  }}</p>
            <h3 class="text-center">Overall Experience</h3>
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
              <!-- Overall Experience -->
              <div class="row row-flex align-items-center survey-mood">
                <div class="col-xs-8 col-flex survey-mood__col">
                  <input type="range" id="survey_mood" name="survey_mood">        
                </div>
                <div class="col-xs-4 col-flex">
                  <div class="survey-mood__color survey-mood__dog-bg pull-right">
                    <div class="survey-mood__dog">
                      <svg id="survey-mood__dog-svg">
                        <g>
                          <defs>
                            <rect id="SVGID_1_" y="0" width="70" height="74"/>
                          </defs>
                          <clipPath id="SVGID_2_">
                            <use xlink:href="#SVGID_1_"  style="overflow:visible;"/>
                          </clipPath>
                          <g class="st0">
                            <path class="st2" d="M40.9,67.6c-1.1-0.6-2.5-0.5-3.6,0c-1.3,0.6-2.2,1.9-2.1,3.4c0.2,1.6,1.6,2.9,3.3,2.9c1.6,0,3.6-0.9,4.1-2.6
                              C43,69.9,42.2,68.4,40.9,67.6z"/>
                            <path class="st3" d="M36.5,67.7c0,0.1,0.1,0.2,0.1,0.2c0.1,0.1,0.2,0,0.2,0c0.5-0.2,1.1-0.4,1.6-0.6c0.1-0.1,0.2-0.1,0.3-0.2
                              c0.1-0.1,0-0.2-0.1-0.3c-0.3-0.6-0.6-1.3-1-1.9c-0.1-0.2-0.2-0.3-0.4-0.3c-0.2-0.1-0.4,0-0.5,0.1c-0.3,0.1-0.9,0.3-1.2,0.5
                              c-0.2,0.3-0.1,0.2,0,0.5C36.1,66.3,36.3,67,36.5,67.7z"/>
                            <path class="st4" d="M67.4,23c-0.4-1.4-1.3-2.6-2.5-3.5c-2.5-2-5.9-2.9-9-2.9c-1.6,0-3.3,0.3-4.9,0.8c-0.7,0.2-1.5,0.5-2.2,0.9
                              c-0.6,0.4-1.1,0.9-1.7,1.3c-0.6,0.5-1.3,0.5-2.1,0.3c-0.8-0.2-1.5-0.6-2.2-1c-1.5-0.8-3.1-1.3-4.8-1.7c-1.8-0.3-3.5-0.5-5.3-0.4
                              c-0.9,0.1-1.8,0.1-2.6,0.3c-0.4,0.1-0.8,0.2-1.2,0.2c-0.2,0.1-0.5,0.2-0.7,0.2c-0.1,0-0.1,0.1-0.2-0.1c-0.1-0.1-0.2-0.2-0.4-0.3
                              c-2.6-2.2-4.5-5.1-7.3-7c-1.2-0.8-2.7-1.5-4.2-1.5c-1.3-0.1-2.7,0.3-3.8,1c-1.1,0.8-1.8,2-1.8,3.3c-0.1,1.4,0.5,2.8,1.3,3.9
                              c2,2.6,5.6,3.1,8.7,3.2c1.9,0.1,3.7-0.1,5.6,0.2c0,1.4-0.4,2.7-0.9,4c-0.2,0.5-0.4,1-0.6,1.6c-0.1,0.2-0.2,0.3-0.3,0.5
                              c0,0.1-0.1,0.1-0.1,0.1c0,0-0.1-0.1,0-0.1c-0.1,0-0.4-0.1-0.5-0.1c-3.6-1-7.2-3.6-11.1-2.4c-1.5,0.4-2.8,1.4-3.6,2.7
                              c-0.9,1.5-1.1,3.2-0.8,4.8c0.5,3.3,2.6,6.4,5,8.7c1.3,1.3,2.8,2.4,4.4,3.3c0.8,0.4,1.6,0.8,2.4,1.2c0.4,0.2,0.8,0.5,1,0.8
                              c0.4,0.5,0.3,1.1,0.2,1.7c-0.3,2-0.6,3.9-0.9,5.9c-0.2,1-0.3,1.9-0.5,2.9c-0.1,0.7-0.4,1.5,0,2.2c0.4,0.6,1,1.1,1.6,1.5
                              c0.7,0.4,1.5,0.8,2.3,1c0.8,0.3,1.7,0.5,2.6,0.5c0.9,0.1,1.8,0.1,2.8,0.1c3.6,0.1,7.1-0.6,10.5-1.9c1.2-0.5,2.2-0.9,3.3-1.6
                              c0.5-0.3,1.1-0.6,1.5-1c0.3-0.2,0.7-0.5,0.9-0.9c0.2-0.3,0.2-0.6,0.2-1c0.1-0.7,0.2-1.4,0.2-2.1c0.2-2,0.4-4,0.5-5.9
                              c0.1-1.8,0.3-3.6,0.5-5.4c0.2-2.3,0.7-4.4,0.9-6.7c0.2-1.9,0.3-3.8,0.5-5.8c0.1-0.8,0.2-1.6,0.2-2.4c0-0.5-0.1-1.4,0.4-1.7
                              c0.5-0.3,1.5,0.1,1.9,0.3c0.8,0.3,1.5,0.6,2.2,1c1.6,0.8,3.3,1.7,5,2.2c1.7,0.5,3.5,0.8,5.3,0.4c1.3-0.3,2.6-1.1,3.4-2.2
                              C67.5,25.8,67.8,24.4,67.4,23z"/>
                            <path class="st3" d="M20.1,58.4c-1,1.3-0.6,3.4,0.6,4.6c1.2,1.2,2.8,1.8,4.4,2.2c5.2,1.2,10.8,0.5,15.6-1.8
                              c1.2-0.6,2.5-1.3,3.3-2.4c0.9-1.2,1.1-2.8,1.2-4.3c-4.2,2.5-8.8,4.1-13.6,4.7c-2,0.2-4.1,0.2-6.1-0.2c-2-0.4-4-1.3-5.4-2.7"/>
                            <path class="st5" d="M36.4,57.9c-0.2-1.7-0.2-3.5-0.8-5.1c-0.5-1.3-1.4-2.4-2.8-3c-1.3-0.6-2.9-0.7-4.3-0.6
                              c-1.6,0.1-3.2,0.5-4.4,1.5c-1.3,1-2.1,2.6-2.5,4.1c-0.2,0.9-0.3,1.7-0.4,2.6c-0.1,0.6-0.2,1.3,0,2c0.4,1.3,2,1.7,3.2,1.9
                              c3.5,0.6,7.1,0.6,10.7,0.1c0.5-0.1,1.5,0,1.5-0.8c0-0.4-0.1-0.8-0.1-1.2C36.5,58.8,36.5,58.4,36.4,57.9z"/>
                            <path class="st5" d="M42.7,21.2c-1.6-0.9-3.7-0.9-5.4-0.2c-2,0.9-3.5,2.7-3.5,4.9c0.1,2.6,2.2,4.7,4.7,4.9
                              c2.6,0.2,5.7-1.2,6.6-3.8C45.7,24.8,44.7,22.3,42.7,21.2z"/>
                            <path class="st6 st6__outline" d="M68.2,24.4c-0.1-1.6-0.8-3-1.9-4.1c-2.2-2.3-5.4-3.6-8.5-3.9c-3.2-0.4-6.7,0.2-9.5,1.8
                              c-0.5,0.3-0.9,0.7-1.4,1c-0.2,0.1-0.5,0.3-0.7,0.3c-0.2,0.1-0.4-0.1-0.6-0.2c-0.3-0.2-0.6-0.3-0.9-0.5c-0.4-0.2-0.8-0.3-1.2-0.5
                              c-0.8-0.3-1.6-0.6-2.5-0.8c-1.7-0.5-3.4-0.8-5.2-0.9c-0.9-0.1-1.8-0.1-2.7-0.1c-0.6,0-1.3,0-2,0.1c-0.9,0.2-1.8,0.4-2.6,0.5
                              c-0.1,0-0.2,0-0.2,0c-0.1-0.1-0.2-0.1-0.2-0.1c-0.2-0.2-0.4-0.3-0.5-0.5c-0.6-0.6-1.2-1.2-1.7-1.8c-2.1-2.3-4.2-4.8-7.3-5.9
                              c-2.6-0.9-5.9-0.6-7.6,1.7c-0.8,1.1-1.1,2.6-0.9,3.9c0.2,1.4,1,2.6,2.1,3.5c1.2,0.9,2.7,1.5,4.2,1.9c1.6,0.5,3.3,0.8,5,0.9
                              c1.3,0.1,2.8,0.1,4.1-0.1c-0.6,1.7-1.2,3.5-1.6,5.3c-2.4-0.8-4.8-1.7-7.1-2.6c-1.1-0.4-2-0.9-3.1-1.2c-0.8-0.3-1.8-0.5-2.6-0.1
                              c-1,0.3-1.6,1.2-2,2.1c0,0-0.1,0.1-0.1,0.1c-0.1,0.3-0.2,0.6-0.4,0.9c-1.2,3.4-1.1,7.2,0.5,10.5c1.5,3.3,4.2,5.9,7.3,7.8
                              c1.5,0.9,3,1.6,4.6,2.2c-0.5,3.6-1,7.2-1.5,10.8c-0.1,0.4-0.1,0.8-0.2,1.2c0,0.1-0.1,0.1-0.1,0.2c-0.1,0.5-0.1,0.9-0.2,1.3
                              c-0.1,0.8-0.1,1.7,0.1,2.4c0.4,1.3,1.4,2.2,2.5,2.9c1.2,0.7,2.6,1,4,1.3c1.6,0.3,3.3,0.4,4.9,0.4c1.5-0.1,3-0.2,4.6-0.5
                              c0.4,0.6,0.6,1.3,0.9,2c0.1,0.1,0.1,0.2,0.2,0.2c-1.2,1-1.6,2.8-0.9,4.3c0.3,0.7,0.9,1.3,1.6,1.6c0.8,0.3,1.8,0.3,2.6,0.2
                              c1.6-0.2,3-1.2,3.3-2.9c0.2-1.6-0.5-3.1-1.8-4c-0.6-0.4-1.3-0.6-2-0.5c-0.3-0.6-0.6-1.2-0.9-1.8c0.6-0.2,1.3-0.5,2-0.8
                              c1.4-0.6,2.8-1.3,3.9-2.4c1.1-1.2,1.5-2.7,1.8-4.3c0.2-1.7,0.4-3.5,0.6-5.2c0.2-1.8,0.4-3.7,0.6-5.5c0.4-3.7,0.8-7.3,1.2-10.9
                              c0.2-1.9,0.4-3.7,0.6-5.5c0.1-0.9,0.2-1.8,0.3-2.7c0.1-0.5,0.1-0.9,0.1-1.3c0-0.2,0-0.6,0.3-0.8c0.2-0.1,0.6,0.1,0.8,0.2
                              c0.4,0.2,0.6,0.4,0.9,0.6c0.6,0.5,1.3,0.9,2,1.3c1.3,0.8,2.8,1.4,4.3,1.8c3,0.8,6.7,1.2,9.2-0.9C67.6,27.3,68.2,25.9,68.2,24.4z
                              M36.2,65.3c0.3-0.1,0.6-0.2,0.9-0.2c0.3,0.6,0.5,1.2,0.8,1.7c-0.3,0.1-0.6,0.2-0.9,0.4C36.9,66.5,36.6,65.9,36.2,65.3z M40,67.7
                              c1,0.5,1.8,1.6,1.8,2.7c0,1.2-0.8,2.1-2,2.4c-1.2,0.3-2.6,0.2-3.3-0.8c-0.8-1.1-0.4-2.6,0.6-3.5C37.8,67.9,39.1,67.2,40,67.7z
                              M43.6,60.4c-0.8,1.2-2.2,1.9-3.5,2.5c-1.4,0.6-2.9,1.2-4.4,1.5c-3,0.7-6.2,0.8-9.2,0.3c-2.3-0.3-5.7-1.1-6.3-3.7
                              c-0.2-0.7-0.1-1.4,0-2.1c2.2,2.1,5.4,2.9,8.3,3c3.8,0.2,7.6-0.5,11.1-1.9c1.8-0.7,3.5-1.6,5.1-2.7C44.5,58.4,44.2,59.5,43.6,60.4z
                              M67.1,25.3c-0.2,1.3-1.2,2.3-2.4,2.9c-1.4,0.7-3.1,0.7-4.6,0.5c-3-0.4-5.9-1.6-8.4-3.4c-0.6-0.4-1.1-0.9-1.8-1
                              c-0.6-0.2-1.3,0.1-1.6,0.6c-0.2,0.3-0.2,0.7-0.2,1c-0.1,0.5-0.1,0.9-0.2,1.4c-0.1,0.9-0.2,1.7-0.3,2.6c-0.4,3.5-0.8,7-1.1,10.5
                              c-0.4,3.5-0.8,7-1.1,10.5c-0.2,1.7-0.4,3.4-0.5,5c-0.1,0-0.1,0.1-0.1,0.1c-3,2.1-6.4,3.6-10,4.3c-3.3,0.6-7,0.8-10.2-0.2
                              c-1.6-0.5-3.1-1.3-4.2-2.6c0.1-0.5,0.1-1,0.2-1.6c0.2-1.7,0.5-3.5,0.8-5.2c0.2-1.6,0.5-3.3,0.7-4.9c2,0.6,4.1,1.1,6.1,1.5
                              c0.5,0.1,1.1,0.2,1.5,0.2c0.3,0.1,0.5-0.1,0.6-0.3c0.1-0.2-0.1-0.6-0.4-0.6c-7.3-1-15.2-3.5-19.1-10.1c-0.9-1.6-1.6-3.3-1.8-5.1
                              c-0.2-1.3-0.1-2.7,0.2-4.1c0.4,1.2,1.1,2.2,2,2.9c0.4,0.2,0.7,0.4,1,0.5c-0.2,1.9,0.2,3.8,1.3,5.4c0.2,0.2,0.5,0.3,0.7,0.2
                              c0.2-0.1,0.4-0.5,0.2-0.7c-1-1.4-1.5-3.1-1.2-4.8c1.5-0.3,2.8-1.6,3.5-2.9c0.5-0.9,0.8-2,0.9-3c3.9,1.5,7.8,2.9,11.7,4.1
                              c0.6,0.2,0.9-0.8,0.3-1c-1.6-0.5-3.1-1-4.6-1.5c0.5-2.1,1.1-4.1,1.9-6.1c0.2-0.4-0.3-0.7-0.6-0.6c-3.3,0.5-6.6,0.2-9.7-0.8
                              c-1.3-0.4-2.8-0.9-3.8-1.9c-1-0.9-1.5-2-1.5-3.4c0.1-1.2,0.6-2.4,1.6-3.2c1-0.8,2.3-1,3.6-1c2.9,0.1,5.3,2.3,7.2,4.3
                              c1.1,1.2,2.1,2.4,3.3,3.6c0.4,0.4,0.9,1,1.5,1c0.4,0,0.8-0.1,1.2-0.1c0.5-0.1,1-0.2,1.5-0.3c0.2-0.1,0.4-0.1,0.5-0.1
                              c0.1,0,0.1,0,0.2-0.1c-0.1,0,0.2,0,0.3,0c1.7-0.1,3.5-0.1,5.2,0.1c1.6,0.2,3.3,0.6,4.9,1c0.8,0.2,1.6,0.5,2.3,0.9
                              c0.5,0.3,1.1,0.7,1.7,0.8c1.2,0.2,2-1,3-1.5c1.4-0.8,2.9-1.3,4.5-1.5c3-0.5,6.3-0.2,9,1.2c1.3,0.6,2.6,1.5,3.5,2.6
                              C66.8,22.4,67.4,23.8,67.1,25.3z"/>
                            <!-- Initial -->
                            <path class="st6 st6__eyes st6--0" d="M28.5,25.7c0.4-0.8,1.2-1.2,2.1-1.2c0.8,0,2,0.5,1.7,1.5c-0.2,0.5,0.6,0.7,0.8,0.2c0.5-1.4-1-2.3-2.2-2.4
                              c-1.2-0.1-2.5,0.5-3.1,1.6C27.6,25.8,28.3,26.2,28.5,25.7z"/>
                            <path class="st6 st6__eyes st6--0" d="M36.7,26.6c0.5-0.5,1.2-1,2-0.9c0.6,0.1,1.3,0.6,1.1,1.3c-0.1,0.5,0.6,0.8,0.8,0.2c0.2-1-0.5-2.1-1.5-2.3
                              c-1.2-0.2-2.2,0.3-3,1.2C35.7,26.4,36.3,27,36.7,26.6z"/>
                            <path class="st6 st6__mouth st6--0" d="M37.5,38.6c-1.2,1.8-3.2,3.1-5.4,3.3c-2.3,0.1-4.4-0.9-5.9-2.6c-0.2-0.3-0.6-0.2-0.9,0
                              c-0.2,0.2-0.2,0.6,0,0.9c1.6,2,4.3,3.1,6.9,2.9c2.6-0.2,5-1.6,6.3-3.8C39,38.5,37.9,37.9,37.5,38.6z"/>
                            <!-- Awful -->
                            <path class="st6 st6__mouth st6--1" style="display: none;" d="M40.9,26.4c-0.4,0.2-0.8,0.3-1.3,0.3c-0.6,0-1.6-0.3-2-0.9c-0.2-0.2-1.2,0.2-0.8,0.6
                              c0.6,0.7,1.5,1.2,2.4,1.2c0.6,0.1,1.2,0,1.7-0.2C41.9,27.2,41.5,26.1,40.9,26.4z"/>
                            <path class="st6 st6__mouth st6--1" style="display: none;" d="M31.2,24.4c-0.4,0.2-0.8,0.3-1.3,0.3c-0.6,0-1.6-0.3-2-0.9c-0.2-0.2-1.2,0.2-0.8,0.6
                              c0.6,0.7,1.5,1.2,2.4,1.2c0.6,0.1,1.2,0,1.7-0.2C32.1,25.2,31.7,24.2,31.2,24.4z"/>
                            <path class="st6 st6__mouth st6--1" style="display: none;" d="M22.5,43.4c0.9-1.5,2.6-2.6,4.4-2.7c1.9-0.1,3.6,0.7,4.9,2.1c0.2,0.2,0.5,0.2,0.7,0c0.2-0.2,0.2-0.5,0-0.7
                              c-1.3-1.6-3.5-2.6-5.7-2.4c-2.1,0.2-4.1,1.3-5.2,3.1C21.2,43.4,22.1,43.9,22.5,43.4z"/>
                            <!-- Bad -->
                            <path class="st6 st6__eyes st6--2" style="display: none;" d="M40.9,26.4c-0.4,0.2-0.8,0.3-1.3,0.3c-0.6,0-1.6-0.3-2-0.9c-0.2-0.2-1.2,0.2-0.8,0.6c0.6,0.7,1.5,1.2,2.4,1.2
                              c0.6,0.1,1.2,0,1.7-0.2C41.9,27.2,41.5,26.1,40.9,26.4z"/>
                            <path class="st6 st6__eyes st6--2" style="display: none;" d="M31.2,24.4c-0.4,0.2-0.8,0.3-1.3,0.3c-0.6,0-1.6-0.3-2-0.9c-0.2-0.2-1.2,0.2-0.8,0.6c0.6,0.7,1.5,1.2,2.4,1.2
                              c0.6,0.1,1.2,0,1.7-0.2C32.1,25.2,31.7,24.2,31.2,24.4z"/>
                            <path class="st6 st6__mouth st6--2" style="display: none;" d="M24.2,41.6c0.8-0.5,1.8-0.9,2.7-0.9c1.1-0.1,2.1,0.2,3,0.6c0.7,0.3,1.4-0.4,0.6-0.8c-1.1-0.6-2.5-0.9-3.7-0.8
                              c-1,0.1-1.9,0.3-2.8,0.8C23,41.1,23.5,42.1,24.2,41.6z"/>
                            <!-- Okey -->
                            <path class="st6 st6__eyes st6--3" style="display: none;" d="M40.9,26.4c-0.4,0.2-0.8,0.3-1.3,0.3c-0.6,0-1.6-0.3-2-0.9c-0.2-0.2-1.2,0.2-0.8,0.6c0.6,0.7,1.5,1.2,2.4,1.2
                              c0.6,0.1,1.2,0,1.7-0.2C41.9,27.2,41.5,26.1,40.9,26.4z"/>
                            <path class="st6 st6__eyes st6--3" style="display: none;" d="M31.2,24.4c-0.4,0.2-0.8,0.3-1.3,0.3c-0.6,0-1.6-0.3-2-0.9c-0.2-0.2-1.2,0.2-0.8,0.6c0.6,0.7,1.5,1.2,2.4,1.2
                              c0.6,0.1,1.2,0,1.7-0.2C32.1,25.2,31.7,24.2,31.2,24.4z"/>
                            <!-- Good -->
                            <path class="st6 st6__eyes st6--4" style="display: none;" d="M27.6,25.1c0.4-0.2,0.8-0.3,1.3-0.3c0.6,0,1.6,0.3,2,0.9c0.2,0.2,1.2-0.2,0.8-0.6c-0.6-0.7-1.5-1.2-2.4-1.2
                              c-0.6-0.1-1.2,0-1.7,0.2C26.6,24.3,27,25.4,27.6,25.1z"/>
                            <path class="st6 st6__eyes st6--4" style="display: none;" d="M37.3,27.1c0.4-0.2,0.8-0.3,1.3-0.3c0.6,0,1.6,0.3,2,0.9c0.2,0.2,1.2-0.2,0.8-0.6c-0.6-0.7-1.5-1.2-2.4-1.2
                              c-0.6-0.1-1.2,0-1.7,0.2C36.3,26.3,36.8,27.3,37.3,27.1z"/>
                            <path class="st6 st6__mouth st6--4" style="display: none;" d="M31.9,41.5c-1.4,1.1-3.3,1.5-5.1,1c-1.8-0.5-3.2-1.9-3.8-3.7c-0.1-0.3-0.5-0.3-0.7-0.2
                              c-0.3,0.1-0.4,0.4-0.2,0.7c0.7,2,2.4,3.6,4.5,4.2c2,0.6,4.3,0.1,6-1.2C33.1,42,32.4,41.1,31.9,41.5z"/>
                            <!-- Amazing -->
                            <path class="st6 st6__eyes st6--5" d="M28.5,25.7c0.4-0.8,1.2-1.2,2.1-1.2c0.8,0,2,0.5,1.7,1.5c-0.2,0.5,0.6,0.7,0.8,0.2c0.5-1.4-1-2.3-2.2-2.4
                              c-1.2-0.1-2.5,0.5-3.1,1.6C27.6,25.8,28.3,26.2,28.5,25.7z"/>
                            <path class="st6 st6__eyes st6--5" d="M36.7,26.6c0.5-0.5,1.2-1,2-0.9c0.6,0.1,1.3,0.6,1.1,1.3c-0.1,0.5,0.6,0.8,0.8,0.2c0.2-1-0.5-2.1-1.5-2.3
                              c-1.2-0.2-2.2,0.3-3,1.2C35.7,26.4,36.3,27,36.7,26.6z"/>
                            <path class="st6 st6__mouth st6--5" d="M37.5,38.6c-1.2,1.8-3.2,3.1-5.4,3.3c-2.3,0.1-4.4-0.9-5.9-2.6c-0.2-0.3-0.6-0.2-0.9,0
                              c-0.2,0.2-0.2,0.6,0,0.9c1.6,2,4.3,3.1,6.9,2.9c2.6-0.2,5-1.6,6.3-3.8C39,38.5,37.9,37.9,37.5,38.6z"/>
                          </g>
                        </g>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Ratings -->
              <div class="rating">
                <div class="row rating__row">
                  <div class="col-xs-6">
                    <p><strong>Grooming Quality</strong></p>
                  </div>
                  <div class="col-xs-6">
                    <div class="starrr pull-right" id="rating_quality" data-rating="0"></div>
                    <input type="hidden" class="rating__stars" id="rating_quality_id" name="rating_quality" value=""/>
                    <input type="hidden" id="appointment_id" name="appointment_id" value='{{ isset($appointment_id) ? $appointment_id : '' }}'/>
                  </div>
                </div>
                <div class="row rating__row">
                  <div class="col-xs-6">
                    <p><strong>Cleanliness</strong></p>
                  </div>
                  <div class="col-xs-6">
                    <div class="starrr pull-right" id="rating_cleanliness" data-rating="0"></div>
                    <input type="hidden" class="rating__stars" id="rating_cleanliness_id"  name="rating_cleanliness" value=""/>
                  </div>
                </div>
                <div class="row rating__row">
                  <div class="col-xs-6">
                    <p><strong>Scheduling</strong></p>
                  </div>
                  <div class="col-xs-6">
                    <div class="starrr pull-right" id="rating_scheduling" data-rating="0"></div>
                    <input type="hidden" class="rating__stars" id="rating_scheduling_id"  name="rating_scheduling" value=""/>
                  </div>
                </div>
                <div class="row rating__row">
                  <div class="col-xs-6">
                    <p><strong>Value</strong></p>
                  </div>
                  <div class="col-xs-6">
                    <div class="starrr pull-right" id="rating_value" data-rating="0"></div>
                    <input type="hidden" class="rating__stars" id="rating_value_id" name="rating_value" value=""/>
                  </div>
                </div>
                <div class="row rating__row">
                  <div class="col-xs-6">
                    <p><strong>Customer Support</strong></p>
                  </div>
                  <div class="col-xs-6">
                    <div class="starrr pull-right" id="rating_cs" data-rating="0"></div>
                    <input type="hidden" class="rating__stars" id="rating_cs_id" name="rating_cs"  value=""/>
                  </div>
                </div>
              </div>
              <!-- Suggestions -->
              <div class="row suggestions">
                <div class="col-xs-12">
                  <div class="form-group">
                    <label class="suggestions__label" for="suggestions">Suggestions</label>
                    <textarea class="form-control" id="suggestions" name="suggestions" rows="5"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Submit -->
          <div class="row">
            <div class="col-md-2 col-md-offset-5 col-sm-4 col-sm-offset-4 col-xs-6 col-xs-offset-3 text-center">
              <button type="button" onclick="submit_form()" class="btn btn-default btn-block survey__submit">Submit</button>
            </div>
          </div>
        </div>
      </form>
    </section>
  <!-- No appt to rate -->
  <section id="no_appt_msg" class="survey survey--no-appt mt-0 mt-3" style="display:none;">
    <div class="container">
      <div class="row text-center">
        <h3>Rate your experience</h3>
        <br>
        <p>Currently, there isn’t any appointment to rate.<br>
           Does your pet need another grooming session?</p>
          <div class="col-sm-4 col-sm-offset-4 col-xs-8 col-xs-offset-2 mt-3">
            <a href="/user/schedule/select-dog" class="btn btn-default btn-block survey__submit">Schedule Appointment</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<footer>
  <div class="container">
    <div class="row">
      <div class="col-lg-10 col-lg-offset-1">
        <p class="text-uppercase text-center">&copy; 2020 Groomit - Made with love in NYC.</p>
      </div>
      <!-- /col -->
    </div>
    <!-- /row -->
  </div>
  <!-- /container -->
</footer>

<script type="text/javascript">

    var onload_func = window.onload;
    window.onload = function() {
        if (onload_func) {
            onload_func();
        }

        initRangeSlider();

      @if ($errors->has('exception'))
          @if( strpos($errors->first('exception'), 'login first') > 0 )
             show_login();
          @elseif( strpos($errors->first('exception'), "can't find your appointment") > 0 )
             $("#no_appt_msg").show();
             $("#appt_rates").hide();
          @endif
      @endif;
    }

    function initRangeSlider() {

      var sliderOptions = {
            min: 1,
            max: 5,
            step: 1,
            precision: 0,
            orientation:'horizontal',
            value: 0,
            lock_to_ticks: true,
            ticks: [1, 2, 3, 4, 5],
            ticks_labels: ['Awful', 'Bad', 'Okay', 'Good', 'Amazing'],
            enabled: false
      }

      var rangeSlider, rating, sumRating;
      var averageRating = 0;
      var ratedAreas = 0;

      rangeSlider = $("#survey_mood").slider(sliderOptions);

      setTimeout(function(){ 

        //Remove the selected class to Awful label on init
        $('.survey-mood .slider-tick-label').removeClass('label-in-selection label-is-selection');

        //Add a general class to the slider to apply colors
        $('.survey-mood .slider').addClass('survey-mood__range');


      }, 500);

      //Update mood according to the average of all star ratings
      $('.starrr').on('starrr:change', function(e, value) {

        sumRating = 0;
        rating = $(this).attr('id');
        $('input[name=' + rating + ']').val(value);

        ratedAreas = $("input.rating__stars").filter(function () {
                      return $.trim($(this).val()).length !== 0
                    }).length;

        $('input.rating__stars').each(function() {
          sumRating += Number($(this).val());
        });

        averageRating = Math.round(sumRating / ratedAreas);

        if (!averageRating) {

          console.log(averageRating);

          rangeSlider.slider('setValue', 0, true, true);
          setMood(0);
          $('.survey-mood .slider-tick-label').removeClass('label-in-selection label-is-selection');

        } else {

          rangeSlider.slider('setValue', averageRating, true, true);

        }

      });
      

      rangeSlider.on('change', function(event) {
        //console.log(event.value.newValue);
        
        setMood(event.value.newValue);

      });
    }

    //Apply class to elements to set color transitions
    function setMood(moodID) {

      for (var i = 0; i < 6; i++) {

        if (i == moodID) {

          $('.survey-mood__color').addClass('survey-mood__color--' + i);
          $('.survey-mood__range').addClass('survey-mood__range--' + i);
          $('.st6--' + i).css('display', 'initial');

        } else {

          $('.survey-mood__color').removeClass('survey-mood__color--' + i);
          $('.survey-mood__range').removeClass('survey-mood__range--' + i);
          $('.st6--' + i).css('display', 'none');

        }

      }
      
    }

    //Listen to the rating changes to update the Overall Experience slider
    function initRatings(slider) {

      
    }

    function submit_form(){
      var quality= $('#rating_quality_id').val();
      var clean= $('#rating_cleanliness_id').val();
      var scheduling= $('#rating_scheduling_id').val();
      var value= $('#rating_value_id').val();
      var cs= $('#rating_cs_id').val();

      if(quality > 0 && quality <= 5){

      }else{
        alert('Please fill in the quality section of the survey');
        return;
      }
      if(clean > 0 && clean <= 5){

      }else{
        alert('Please fill in the cleanliness section of the survey');
        return;
      }
      if(scheduling > 0 && scheduling <= 5){

      }else{
        alert('Please fill in the scheduling section of the survey');
        return;
      }
      if(value > 0 && value <= 5){

      }else{
        alert('Please fill in the value section of the survey');
        return;
      }
      if(cs > 0 && cs <= 5){

      }else{
        alert('Please fill in the customer support section of the survey');
        return;
      }

      // alert(rating_quality)
      // alert(rating_quality)
      // alert(rating_quality)

      $.ajax({
        url: '/user/survey',
        data: {
          _token: '{!! csrf_token() !!}',
          rating_quality: $('#rating_quality_id').val(),
          rating_cleanliness: $('#rating_cleanliness_id').val(),
          rating_scheduling: $('#rating_scheduling_id').val(),
          rating_value: $('#rating_value_id').val(),
          rating_cs: $('#rating_cs_id').val(),
          suggestions: $('#suggestions').val(),
          appointment_id: $('#appointment_id').val()

        },
        cache: false,
        type: 'post',
        dataType: 'json',
        success: function(res) {
          if ($.trim(res.msg) === '') {
            alert('Thank you for your support. We will keep improve our services.');
            window.location = "/user/home";
          } else {
            alert(res.msg);
          }
        }
      });


      // alert( $('#rating_quality_id').val() );
      // alert( $('#rating_cleanliness_id').val() );
      // alert( $('#rating_scheduling_id').val() );
      // alert( $('#rating_value_id').val() );
      // alert( $('#rating_cs_id').val() );
      //

    }


</script>
@stop
