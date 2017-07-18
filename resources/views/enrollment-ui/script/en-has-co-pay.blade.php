<div>
    <p><b>ENGLISH [speak clearly and cheerfully]</b>: Hi this is {{auth()->user()->fullName}} calling on behalf of
        <b>Dr. @{{provider_name}}</b> at <b>{{$enrollee->practiceName}}</b>. I’m calling
        for {{$enrollee->first_name}} {{$enrollee->last_name}}. is
        this {{$enrollee->first_name}} {{$enrollee->last_name}}?</p>

    <p>How are you doing today?</p>

    <p>
        The reason I’m calling is @{{provider_name}} is starting to work with a new personalized care program.

        @{{provider_name}} just wanted me to offer it to his or her patients in case it might be helpful.
    </p>

    <p>
        And so what the program is: there would be a registered nurse (RN), we call them “Care Coaches”, who
        would call you twice a month to check on how you’re doing, or to see if you’re having any new
        problems. And then the nurse would report back to [Doctor Name] so that s/he would have that
        information in his/her records. This helps the doctor keep up with how you’re doing in between visits.
    </p>

    <p>
        The program is offered through Medicare and should only be a small ~$8 co-pay. So I was just calling

        today to check if you would be interested in trying this?

        <br><b>[patients then usually have questions...caller may have to reassure patient that they will still see the

            doctor regularly, RN calls are only a supplement to regular care - not a replacement.]</b>
    </p>

    <p>
        <b>[Note: if the patient is hesitant, then stress:]</b>
        <br>“there’s no obligation, it’s just a program from the doctor and you can always try it for a month or two.
        If you don’t like it, just call us and you can be taken off the program.”
    </p>

    <p>
        <b>[if no, “not for me”]:</b>

        <br>
        That’s perfectly fine, if you don’t need that you must be doing really well. No pressure. It’s just a
        program to help the doctor keep up with you between visits.
        <b>[if the patient then becomes curious]</b>
        You’re welcome to give it a try and you can cancel at any time.
    </p>

    <p>
        <b>[if patient says yes:]</b>
        <br>
        The only thing is you can only be a part of one doctor’s care management program at a time. I just want
        to check and make sure that you’re not already signed up for this. If not, then we can enroll you.
        <br>[then collect the rest of the information]:

        <br>[Enroller/Ambassador should fill out patient information in enrollment sheet / Confirm patient’s best
        contact #, preferred call times, e-mail and address. Also collect any specialist data from patient]
    </p>

    <p>
        <b>[After info collected for “yes”]:</b>
        <br>
        “That’s all I need for now, just wanted to let you know that you can cancel at anytime, and that a
        registered nurse will be reaching out to you soon. They’ll be calling from the same number I called you from today.”
    </p>

    <p>“Have a great day! Thanks!”</p>
</div>