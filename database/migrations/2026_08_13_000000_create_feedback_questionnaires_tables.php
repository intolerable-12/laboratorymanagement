<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('feedback_questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_questionnaire_id')
                ->constrained('feedback_questionnaires', indexName: 'fq_questions_fq_id_foreign')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('question_type', ['likert', 'raw']);
            $table->text('question_text');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['feedback_questionnaire_id', 'sort_order'], 'fq_questions_fq_id_sort_order_idx');
        });

        Schema::create('feedback_questionnaire_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_questionnaire_id')
                ->constrained('feedback_questionnaires', indexName: 'fq_responses_fq_id_foreign')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('user_no');
            $table->timestamps();

            $table->unique(['feedback_questionnaire_id', 'user_no'], 'fq_responses_fq_id_user_no_unique');
            $table->foreign('user_no')
                ->references('userNo')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('feedback_questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_questionnaire_response_id')
                ->constrained('feedback_questionnaire_responses', indexName: 'fq_answers_fq_response_id_foreign')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('feedback_questionnaire_question_id')
                ->constrained('feedback_questionnaire_questions', indexName: 'fq_answers_fq_question_id_foreign')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('likert_value')->nullable();
            $table->longText('raw_answer')->nullable();
            $table->timestamps();

            $table->unique(['feedback_questionnaire_response_id', 'feedback_questionnaire_question_id'], 'fq_response_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_questionnaire_answers');
        Schema::dropIfExists('feedback_questionnaire_responses');
        Schema::dropIfExists('feedback_questionnaire_questions');
        Schema::dropIfExists('feedback_questionnaires');
    }
};