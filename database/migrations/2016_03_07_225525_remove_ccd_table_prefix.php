<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCcdTablePrefix extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('lv_ccd_allergy_logs')) {
			Schema::rename('lv_ccd_allergy_logs', 'ccd_allergy_logs');
		}

		if (Schema::hasTable('lv_ccdas')) {
			Schema::rename('lv_ccdas', 'ccdas');
		}

		if (Schema::hasTable('lv_ccd_demographics_logs')) {
			Schema::rename('lv_ccd_demographics_logs', 'ccd_demographics_logs');
		}

		if (Schema::hasTable('lv_ccd_document_logs')) {
			Schema::rename('lv_ccd_document_logs', 'ccd_document_logs');
		}

		if (Schema::hasTable('lv_ccd_import_routines')) {
			Schema::rename('lv_ccd_import_routines', 'ccd_import_routines');
		}

		if (Schema::hasTable('lv_ccd_import_routines_strategies')) {
			Schema::rename('lv_ccd_import_routines_strategies', 'ccd_import_routines_strategies');
		}

		if (Schema::hasTable('lv_ccd_medication_logs')) {
			Schema::rename('lv_ccd_medication_logs', 'ccd_medication_logs');
		}

		if (Schema::hasTable('lv_ccd_problem_logs')) {
			Schema::rename('lv_ccd_problem_logs', 'ccd_problem_logs');
		}

		if (Schema::hasTable('lv_ccd_problems')) {
			Schema::rename('lv_ccd_problems', 'ccd_problems');
		}

		if (Schema::hasTable('lv_ccd_provider_logs')) {
			Schema::rename('lv_ccd_provider_logs', 'ccd_provider_logs');
		}

		if (Schema::hasTable('lv_ccd_vendors')) {
			Schema::rename('lv_ccd_vendors', 'ccd_vendors');
		}

		if (Schema::hasTable('lv_cpm_problems')) {
			Schema::rename('lv_cpm_problems', 'cpm_problems');
		}

		if (Schema::hasTable('lv_q_a_import_outputs')) {
			Schema::rename('lv_q_a_import_outputs', 'q_a_import_outputs');
		}

		if (Schema::hasTable('lv_q_a_import_summaries')) {
			Schema::rename('lv_q_a_import_summaries', 'q_a_import_summaries');
		}

		if (Schema::hasTable('lv_snomed_to_cpm_icd_maps')) {
			Schema::rename('lv_snomed_to_cpm_icd_maps', 'snomed_to_cpm_icd_maps');
		}

		if (Schema::hasTable('lv_snomed_to_icd10_map')) {
			Schema::rename('lv_snomed_to_icd10_map', 'snomed_to_icd10_map');
		}

		Schema::dropIfExists('ccd_problems');
		Schema::dropIfExists('lv_parsed_ccds');
		Schema::dropIfExists('lv_xml_ccds');
		Schema::dropIfExists('lv_qliqsoft_messages_log');
		Schema::dropIfExists('lv_third_party_apis');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// no reverse
	}

}
