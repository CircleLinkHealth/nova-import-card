<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeWholeGrainSuggestion extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $table = 'ppp_task_recommendations';

        $updatedData = json_encode([
            [
                'qualitative_trigger' => 'Poor diet (fruits/veggies)',
                'task_body'           => 'Fruits and vegetables are important part of healthy eating and provide a source of many nutrients, including potassium, fiber, folate (folic acid) and vitamins A, E and C. People who eat fruit and vegetables as part of their daily diet have a reduced risk of many chronic diseases. Your doctor may recommend:',
                'recommendation_body' => ['Getting 4-5 servings of fruits and vegetables a day'],
            ],
            [
                'qualitative_trigger' => ' Poor diet (whole grain)',
                'task_body'           => 'Foods made from grains (wheat, rice, and oats) help form the foundation of a nutritious diet. They provide vitamins, minerals, carbohydrates (starch and dietary fiber), and other substances that are important for good health. Eating plenty of whole grains, such as whole wheat bread or oatmeals may help protect you against many chronic diseases. Experts recommend that all adults eat at least half their grains as whole grains. Your doctor may suggest:',
                'recommendation_body' => ['Aiming for at least 3 servings of whole grains a day'],
            ],
            [
                'qualitative_trigger' => 'Poor diet (fatty/fried foods)',
                'task_body'           => 'A small amount of fat is an essential part of a healthy, balanced diet. Although It\'s fine to enjoy fats, fried foods and sweets occasionally, too much sugar and saturated fat in your diet can raise your cholesterol. This increases the risk of heart disease. Your doctor may recommend:',
                'recommendation_body' => ['Cutting down consumption to <1 servings of fried and high-fat foods a day'],
            ],
            [
                'qualitative_trigger' => 'Poor diet (candy/sugary beverages)',
                'task_body'           => 'The average can of sugar-sweetened (sucrose, high-fructose corn syrup, dextrose, cane sugar etc.) soda or fruit punch provides about 150 calories, almost all of them from sugar, usually high-fructose corn syrup. That’s the equivalent of 10 teaspoons of table sugar. If you were to drink just one can of a sugar-sweetened soft drink every day, and not cut back on calories elsewhere, you could gain up to 5 pounds in a year. People who drink sugary beverages do not feel as full as if they had eaten the same calories from solid food, and studies show that people consuming sugary beverages don’t compensate for their high caloric content by eating less food.  Your doctor may recommend:',
                'recommendation_body' => ['Cutting down consumption to <1 servings of sugar-sweetened beverages / sweets a day'],
            ],
        ]);

        DB::table($table)->where('title', '=', 'Nutrition')->update(['data' => $updatedData]);
    }
}
