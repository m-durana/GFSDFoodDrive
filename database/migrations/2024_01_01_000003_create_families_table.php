<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maps legacy `family` table to Laravel `families` table.
     *
     * Legacy columns: FamilyID, userID, FamilyNumber, FamilyName, Address,
     * Phone1, Phone2, Email, FemaleAdults, MaleAdults, NumberofAdults,
     * Infants, YoungChildren, Children, Tween, Teenager,
     * NumberofChildren, NumberofFamilyMembers,
     * hasCRHSChildren, hasGFHSChildren, PetInformation,
     * DeliveryPreference, DeliveryDate, DeliveryTime,
     * NeedforHelp, SevereNeed, OtherQuestions
     */
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();                                                          // FamilyID
            $table->foreignId('user_id')->constrained()->onDelete('cascade');      // userID FK
            $table->unsignedInteger('family_number')->nullable()->unique();        // FamilyNumber

            // Contact info
            $table->string('family_name');                                         // FamilyName
            $table->string('address');                                             // Address
            $table->string('phone1');                                              // Phone1
            $table->string('phone2')->nullable();                                  // Phone2
            $table->string('email')->nullable();                                   // Email

            // Adult counts
            $table->unsignedSmallInteger('female_adults')->default(0);             // FemaleAdults
            $table->unsignedSmallInteger('male_adults')->default(0);               // MaleAdults
            $table->unsignedSmallInteger('number_of_adults')->default(0);          // NumberofAdults (computed)

            // Child age group counts
            $table->unsignedSmallInteger('infants')->default(0);                   // Infants
            $table->unsignedSmallInteger('young_children')->default(0);            // YoungChildren
            $table->unsignedSmallInteger('children_count')->default(0);            // Children (renamed to avoid conflict)
            $table->unsignedSmallInteger('tweens')->default(0);                    // Tween
            $table->unsignedSmallInteger('teenagers')->default(0);                 // Teenager
            $table->unsignedSmallInteger('number_of_children')->default(0);        // NumberofChildren (computed)
            $table->unsignedSmallInteger('number_of_family_members')->default(0);  // NumberofFamilyMembers (computed)

            // School flags
            $table->boolean('has_crhs_children')->default(false);                  // hasCRHSChildren
            $table->boolean('has_gfhs_children')->default(false);                  // hasGFHSChildren

            // Delivery info
            $table->string('pet_information')->nullable();                         // PetInformation
            $table->string('delivery_preference')->nullable();                     // DeliveryPreference (Delivery/Pickup)
            $table->string('delivery_date')->nullable();                           // DeliveryDate
            $table->string('delivery_time')->nullable();                           // DeliveryTime

            // Need assessment
            $table->text('need_for_help')->nullable();                             // NeedforHelp
            $table->text('severe_need')->nullable();                               // SevereNeed
            $table->text('other_questions')->nullable();                           // OtherQuestions

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
