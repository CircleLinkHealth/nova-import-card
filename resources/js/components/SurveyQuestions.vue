<template>
    <div class="container">
        <div class="card">
            <div class="practice-title">
                <label id="title">[Practice Name]
                    Dr. [doctor last name]’s Office</label>
            </div>
            <img src="https://drive.google.com/uc?export=view&id=14yPR6Z8coudiAzEMTSVQK80BVyZjjqVg"
                 class="welcome-icon" alt="welcome icon">
            <div class="card-body">
                <div class="survey-main-title">
                    <label id="sub-title">Annual Wellness
                        Survey Login</label>
                </div>
                <div class="survey-sub-welcome-text">Welcome to your
                    Annual Wellness Visit (AWV) Questionnaire! Understanding your health is of upmost importance to us,
                    so thank you for taking time to fill this out.
                    If there’s any question you have trouble answering, feel free to click the call button on the bottom
                    left and a representative will help when you call the number. If you skip any questions, our reps
                    will also reach out shortly. Thanks!
                </div>
            </div>
            <a href="#" class="btn btn-primary">Start</a>

            <div class="by-circlelink">
                ⚡️ by CircleLink Health
            </div>
            <br>
            <div class="bottom-navbar">
                <div class="next-previous-buttons">
                    <button type="button" class="btn btn-sm next">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>

<style scoped>
    .practice-title {
        /*  width: 300px;
          height: 58px;*/
        font-family: Poppins, sans-serif;
        font-size: 18px;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 20px;
        /* margin-left: 390px;*/
        color: #50b2e2;
    }

    .practice-title .text-style-1 {
        font-weight: 600;
    }

    .survey-main-title {
        /* width: 400px;
         height: 80px;*/
        font-family: Poppins, sans-serif;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 30px;
        /* margin-left: 325px;*/
        color: #1a1a1a;
    }

    .survey-sub-welcome-text {
        /*width: 800px;
        height: 100px;*/
        font-family: Poppins, sans-serif;
        font-size: 18px;
        font-weight: normal;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        text-align: center;
        margin-top: 25px;
        /*margin-left: 120px;*/
        color: #1a1a1a;
    }

    .btn-primary {
        border-radius: 3px;
        background-color: #50b2e2;
        margin-top: 50px;
        margin-left: 469px;
        width: 160px;
        height: 50px;
    }

    .bottom-navbar {
        background-color: #ffffff;
        border-bottom: 1px solid #808080;

    }

    .by-circlelink {
        /* width: 234px;
         height: 30px;*/
        font-family: Poppins, sans-serif;
        font-size: 18px;
        font-weight: 600;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        margin-top: 10px;
        margin-left: 430px;
        color: #50b2e2;
    }

    .by-circlelink .text-style-1 {
        font-weight: normal;
        color: #1a1a1a;
    }

    .card {
        margin-top: 50px;
        background-color: #f2f6f9;
        border-top: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
    }

    .next-previous-buttons {
        width: 55px;
        margin-top: 10px;
        margin-bottom: 10px;
        margin-left: 1020px;
        height: 55px;
        border-radius: 5px;
        background-color: #50b2e2;
    }

    .next {
        position: center;
    }

    .previous {
        position: center;
    }

    .welcome-icon {
        width: 108px;
        margin-left: 490px;
        margin-top: 20px;
    }

</style>
