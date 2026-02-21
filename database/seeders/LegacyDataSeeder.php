<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDataSeeder extends Seeder
{
    /**
     * Import data from the legacy gfsdfooddrive MySQL database.
     *
     * Prerequisites:
     * 1. Configure LEGACY_DB_* variables in .env
     * 2. Ensure the legacy database is accessible
     *
     * Run with: php artisan db:seed --class=LegacyDataSeeder
     */
    public function run(): void
    {
        $this->command->info('Starting legacy data migration...');

        $this->migrateUsers();
        $this->migrateFamilies();
        $this->migrateChildren();

        $this->command->info('Legacy data migration complete!');
    }

    private function migrateUsers(): void
    {
        $legacyUsers = DB::connection('legacy')->table('tblUser')->get();

        $this->command->info("Migrating {$legacyUsers->count()} users...");

        foreach ($legacyUsers as $user) {
            DB::table('users')->insert([
                'id' => $user->userID,
                'username' => $user->username,
                'first_name' => $user->FirstName,
                'last_name' => $user->LastName,
                'email' => null,
                'password' => $user->password, // Already bcrypt hashed
                'permission' => $user->permission,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Users migrated successfully.');
    }

    private function migrateFamilies(): void
    {
        $legacyFamilies = DB::connection('legacy')->table('family')->get();

        $this->command->info("Migrating {$legacyFamilies->count()} families...");

        foreach ($legacyFamilies as $family) {
            DB::table('families')->insert([
                'id' => $family->FamilyID,
                'user_id' => $family->userID,
                'family_number' => $family->FamilyNumber ?: null,
                'family_name' => $family->FamilyName,
                'address' => $family->Address,
                'phone1' => $family->Phone1,
                'phone2' => $family->Phone2 ?? null,
                'email' => $family->Email ?? null,
                'female_adults' => $family->FemaleAdults ?? 0,
                'male_adults' => $family->MaleAdults ?? 0,
                'number_of_adults' => $family->NumberofAdults ?? 0,
                'infants' => $family->Infants ?? 0,
                'young_children' => $family->YoungChildren ?? 0,
                'children_count' => $family->Children ?? 0,
                'tweens' => $family->Tween ?? 0,
                'teenagers' => $family->Teenager ?? 0,
                'number_of_children' => $family->NumberofChildren ?? 0,
                'number_of_family_members' => $family->NumberofFamilyMembers ?? 0,
                'has_crhs_children' => (bool) ($family->hasCRHSChildren ?? false),
                'has_gfhs_children' => (bool) ($family->hasGFHSChildren ?? false),
                'pet_information' => $family->PetInformation ?? null,
                'delivery_preference' => $family->DeliveryPreference ?? null,
                'delivery_date' => $family->DeliveryDate ?? null,
                'delivery_time' => $family->DeliveryTime ?? null,
                'need_for_help' => $family->NeedforHelp ?? null,
                'severe_need' => $family->SevereNeed ?? null,
                'other_questions' => $family->OtherQuestions ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Families migrated successfully.');
    }

    private function migrateChildren(): void
    {
        $legacyChildren = DB::connection('legacy')->table('child')->get();

        $this->command->info("Migrating {$legacyChildren->count()} children...");

        foreach ($legacyChildren as $child) {
            DB::table('children')->insert([
                'id' => $child->ChildID,
                'family_id' => $child->FamilyID,
                'gender' => $child->Gender,
                'age' => $child->Age,
                'school' => $child->School ?? null,
                'clothes_size' => $child->ClothesSize ?? null,
                'clothing_styles' => $child->ClothingStyles ?? null,
                'clothing_options' => $child->ClothingOptions ?? null,
                'gift_preferences' => $child->GiftPreferences ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Children migrated successfully.');
    }
}
