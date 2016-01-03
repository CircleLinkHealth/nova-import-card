@extends('partials.providerUI')

@section('content')

    <div class="main-form-block main-form-horizontal col-md-12">
        <div class="row">
            <div class="form-block col-md-6">
                <div class="row">
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="activityKey">
                                    Activity Performed:
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select id="activityKey" name="type" class="selectpicker" data-size="10" required disabled>
                                        <option value="">Select Activity</option>
                                        <optgroup label="">
                                            <option value="CCM Welcome Call">CCM Welcome Call</option><option value="General (Clinical)"selected>General (Clinical)</option><option value="Medication Reconciliation">Medication Reconciliation</option><option value="Appointments">Appointments</option><option value="Test (Scheduling, Communications, etc)">Test (Scheduling, Communications, etc)</option><option value="Call to Other Care Team Member">Call to Other Care Team Member</option><option value="Review Care Plan">Review Care Plan</option><option value="Review Patient Progress">Review Patient Progress</option><option value="Transitional Care Management Activities">Transitional Care Management Activities</option><option value="Other">Other</option>                                                            </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="performedBy">
                                    Performed By:
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select id="performedBy" name="provider_id" class="selectpicker" data-size="10" disabled>

                                        <option value="169" >
                                            Linda Warshavsky                                                                </option>

                                        <option value="292" >
                                            Dr R Aph                                                                </option>

                                        <option value="295" selected>
                                            CF Doctor                                                                </option>

                                        <option value="391" >
                                            Rohan Man                                                                </option>

                                        <option value="400" >
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-block col-md-6">
                <div class="row">
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="activityDate">
                                    When (Patient Local Time):
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input readonly name="performed_at" type="datetime-local" class="selectpicker" data-width="95px" data-size="10" list max="2015-12-31T18:58"
                                           value="2015-12-31T18:14">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="activityValue">
                                    For how long?
                                </label>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="duration" id="activityValue" class="selectpicker" data-size="10" disabled>
                                    <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7"selected>7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option><option value="60">60</option><option value="61">61</option><option value="62">62</option><option value="63">63</option><option value="64">64</option><option value="65">65</option><option value="66">66</option><option value="67">67</option><option value="68">68</option><option value="69">69</option><option value="70">70</option><option value="71">71</option><option value="72">72</option><option value="73">73</option><option value="74">74</option><option value="75">75</option><option value="76">76</option><option value="77">77</option><option value="78">78</option><option value="79">79</option><option value="80">80</option><option value="81">81</option><option value="82">82</option><option value="83">83</option><option value="84">84</option><option value="85">85</option><option value="86">86</option><option value="87">87</option><option value="88">88</option><option value="89">89</option><option value="90">90</option><option value="91">91</option><option value="92">92</option><option value="93">93</option><option value="94">94</option><option value="95">95</option><option value="96">96</option><option value="97">97</option><option value="98">98</option><option value="99">99</option><option value="100">100</option><option value="101">101</option><option value="102">102</option><option value="103">103</option><option value="104">104</option><option value="105">105</option><option value="106">106</option><option value="107">107</option><option value="108">108</option><option value="109">109</option><option value="110">110</option><option value="111">111</option><option value="112">112</option><option value="113">113</option><option value="114">114</option><option value="115">115</option><option value="116">116</option><option value="117">117</option><option value="118">118</option><option value="119">119</option><option value="120">120</option>                                                    </select>
                                <input type="hidden" name="duration_unit" value="seconds">
                                <input type="hidden" name="patient_id" value="308">
                                <input type="hidden" name="logged_from" value="manual_input">
                                <input type="hidden" name="logger_id" value="391">
                                <input type="hidden" name="activity_id" value="3135">                                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="new-activity-item">
                <div class="form-group">
                    <div class="col-sm-12">
                        <input type="hidden" name="meta[0][meta_key]" value="comment">
                        <textarea readonly class="form-control" placeholder="Enter Comment..." name="meta[0][meta_value]">er</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2"><BR><strong>Note: Clinical Call time entered manually should not include time spent in the <i>CarePlanManager</i> portal while viewing or inputting/changing patient information and care plans.</strong></div>
            </div>
            <div class="form-item form-item-spacing text-center">
                <input type="hidden" value="update_activity" />
                <button id="update" name="submitAction" type="submit" value="back" class="btn btn-primary btn-lg form-item--button form-item-spacing">Return to Notes/Offline Activities</button>
            </div>
            <div class="form-item form-item-spacing text-center">
            </div>

        </div>
    </div>
    </form>
    </div>
    </div>
    </div>
    </section>
    </div>


@stop