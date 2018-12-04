<template>
    <div>
        <p><b>ENGLISH [speak clearly and cheerfully]</b>: Hi this is
            {{userFullName}} calling on behalf of
            <b>Dr. {{providerFullName}}</b> at <b>{{practiceName}}</b>. I’m calling
            for {{name}}. is this {{name}}?</p>

        <p>How are you doing today?</p>

        <p>
            The reason I’m calling is {{providerFullName}} is starting to work with a new
            personalized
            care program.

            {{providerFullName}} thinks it might be helpful for you and wants you to enroll.
        </p>

        <p>
            And so what the program is: there would be a registered nurse (RN), who can call you
            once or twice a month,
            whichever you prefer to check on how you’re doing, or to see if you’re having any new
            problems. And then the
            nurse would report back to {{providerFullName}} so that s/he would have that information
            in his/her records.
            This helps the doctor keep up a little better with how you’re doing in between visits
            and keeps them updated on your
            medications and vitals (such as blood pressures and blood sugars).
        </p>

        <p>
            The program is offered through Medicare and it’s no cost if you have supplemental
            insurance to cover a small ~$8 copay.
            So I was just calling today to check if you would be interested in trying this?
            <br><b>[patients then usually have questions...caller may have to reassure patient that
            they will still see the
            doctor regularly, RN calls are only a supplement to regular care - not a replacement.
            ]</b>
        </p>


        <p>
            <b>[Note: if the patient is hesitant, then stress:]</b>
            <br>“It is easy to cancel if you just want to try it for a month or two and see how it
            goes. All you would have to do is
            just let the nurse know that you don’t want to continue and we will take your name back
            off of the list.”
        </p>

        <p>
            <b>[if no, “not for me”]:</b>


            <br>
            That’s perfectly fine, there’s no pressure to sign up.
            This is just a program to help the doctor keep up with you between visits.
            <b>[if the patient then becomes curious]</b>
            You’re welcome to give it a try and you can cancel at any time. Would you like to try it
            out for a month and see how it goes?
            This program is available long term, if you think you’d be interested at a later date -
            would you like us to call back and check in with you then?”
            <br>
            <b>[also - provide patient with phone number to call back if they’d like to enroll
                later]
                [mark patient “soft decline” if the patient might want to enroll in the future]
                [mark patient “hard decline” if the patient isn’t interested now nor will they be
                interested in the future]
            </b>
        </p>

        <p>
            <b>[if patient says yes:]</b>
            <br>
            The only thing is you can only be a part of one doctor’s care management program at a
            time. I just want
            to check and make sure that you’re not already signed up for this. If not, then we can
            go ahead and enroll you.
            Did you prefer that the nurse call you once or twice a month? Are you currently on
            dialysis? Are you receiving hospice services?

            <br>[then collect the rest of the information]:

            <br>[Enroller/Ambassador should fill out patient information in enrollment sheet /
            Confirm patient’s best
            contact #, preferred call times, e-mail and address. Also collect any specialist data
            from patient]
        </p>

        <p>
            <b>[at end of all info collected for “yes”]:</b>
            <br>
            “That’s all I need for now, The nurse will give you a call for the first time within the
            next week and she will
            introduce herself to you and give you the number where you can reach her anytime you
            need to. They’ll be
            calling from the same number I called you from today.”
        </p>

        <p>“Have a great day! Thanks!”</p>


        <p>
            <b>[If Caller Reaches Machine, Leave Voice Message:]</b>​ Hi this is
            {{userFullName}} calling on behalf of <b>Dr. {{providerFullName}}</b>
            at <b>{{practiceName}}</b>. The doctor[s] have invited you to their new
            personalized care management program.
            Please give us a call at [number Ambassador calling from on page 2] to learn more.
            Please note there is
            nothing to worry about, this program just lets the Dr. take better care of you between
            visits. Again the number is [number Ambassador calling from]
        </p>
    </div>
</template>

<script>
    export default {
        name: "no-copay-en",
        props: [
            'name',
            'userFullName',
            'providerFullName',
            'practiceName'
        ]
    }
</script>

<style scoped>

</style>