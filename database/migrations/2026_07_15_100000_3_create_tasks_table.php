<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('start_date');
            $table->date('due_date');
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->string('status')->default('todo');
            $table->string('priority')->default('medium');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('project_id');
            $table->index('milestone_id');
            $table->index('assigned_employee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
