<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('response_protocols', function (Blueprint $table) {
            $table->id();
            
            // Protocol details
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Category relationships
            $table->foreignId('category_id')->nullable()->constrained('incident_categories')->onDelete('set null');
            $table->boolean('applies_to_all_categories')->default(false);
            
            // Response requirements
            $table->json('required_teams')->nullable()->comment('Types of teams required for this protocol');
            $table->json('required_resources')->nullable()->comment('Equipment and resources needed');
            $table->json('external_agencies')->nullable()->comment('External agencies to notify if needed');
            
            // Response timing
            $table->unsignedInteger('target_response_time')->default(15)->comment('Target response time in minutes');
            $table->unsignedInteger('resolution_time_target')->default(60)->comment('Target resolution time in minutes');
            
            // Protocol steps by severity
            $table->json('steps_low')->nullable()->comment('Response steps for low severity');
            $table->json('steps_medium')->nullable()->comment('Response steps for medium severity');
            $table->json('steps_high')->nullable()->comment('Response steps for high severity');
            $table->json('steps_critical')->nullable()->comment('Response steps for critical severity');
            
            // Escalation procedures
            $table->json('escalation_triggers')->nullable()->comment('Conditions that trigger escalation');
            $table->json('escalation_steps')->nullable()->comment('Steps to take when escalating');
            $table->unsignedInteger('auto_escalation_time')->nullable()->comment('Time in minutes after which to auto-escalate');
            
            // Notification and reporting
            $table->json('notification_list')->nullable()->comment('Who to notify for this type of incident');
            $table->boolean('requires_police_report')->default(false);
            $table->boolean('requires_medical_response')->default(false);
            $table->boolean('requires_evacuation_plan')->default(false);
            $table->boolean('requires_evidence_collection')->default(false);
            
            // Documentation and follow-up
            $table->json('required_documentation')->nullable()->comment('Documents that must be completed');
            $table->json('follow_up_actions')->nullable()->comment('Actions required after resolution');
            
            // Meta information
            $table->text('notes')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('last_reviewed')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('version')->default(1);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('priority');
            $table->index(['category_id', 'active']);
            // Use a shorter name for the index
            $table->index(['requires_police_report', 'requires_medical_response'], 'response_protocols_emergency_index');
        });

        // Insert default response protocols
        DB::table('response_protocols')->insert([
            [
                'name' => 'Theft Response Protocol',
                'code' => 'TRP-1',
                'description' => 'Standard protocol for responding to theft incidents on campus',
                'priority' => 'medium',
                'category_id' => 1, // Theft category
                'required_teams' => json_encode(['investigation', 'patrol']),
                'required_resources' => json_encode(['evidence_collection_kit', 'camera', 'incident_forms']),
                'external_agencies' => null,
                'target_response_time' => 15,
                'resolution_time_target' => 120,
                'steps_low' => json_encode([
                    'Dispatch patrol officer to take report',
                    'Document incident details and affected items',
                    'Secure the area if needed',
                    'Provide incident reference number to reporter',
                    'Follow up within 48 hours'
                ]),
                'steps_medium' => json_encode([
                    'Dispatch patrol team to scene immediately',
                    'Secure the area and preserve evidence',
                    'Interview witnesses and collect statements',
                    'Review nearest CCTV footage',
                    'Complete incident report with all details',
                    'Follow up within 24 hours'
                ]),
                'steps_high' => json_encode([
                    'Dispatch investigation team and patrol units immediately',
                    'Secure crime scene with perimeter',
                    'Document and photograph all evidence',
                    'Interview all witnesses',
                    'Review all CCTV in the area',
                    'Report to campus management',
                    'Consider police involvement if high value',
                    'Complete detailed investigation report',
                    'Follow up within 12 hours'
                ]),
                'escalation_triggers' => json_encode([
                    'Value of stolen items exceeds ₦50,000',
                    'Pattern matching previous thefts',
                    'Forced entry or property damage',
                    'Theft from secure/restricted areas'
                ]),
                'notification_list' => json_encode([
                    'security_director' => 'all',
                    'zone_manager' => 'all',
                    'campus_management' => 'high',
                    'police' => 'high'
                ]),
                'requires_police_report' => false,
                'requires_medical_response' => false,
                'requires_evidence_collection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Medical Emergency Protocol',
                'code' => 'MEP-1',
                'description' => 'Protocol for medical emergencies requiring immediate response',
                'priority' => 'critical',
                'category_id' => 5, // Medical Emergency category
                'required_teams' => json_encode(['response', 'emergency']),
                'required_resources' => json_encode(['first_aid_kit', 'AED', 'emergency_contacts', 'stretcher']),
                'external_agencies' => json_encode(['campus_clinic', 'nearest_hospital']),
                'target_response_time' => 5,
                'resolution_time_target' => 30,
                'steps_medium' => json_encode([
                    'Dispatch nearest security personnel with first aid training',
                    'Contact campus clinic',
                    'Provide basic first aid if trained',
                    'Arrange transport to medical facility if needed',
                    'Document incident details'
                ]),
                'steps_high' => json_encode([
                    'Dispatch rapid response team with medical kit',
                    'Alert campus clinic for immediate response',
                    'Clear area and ensure access for medical personnel',
                    'Provide emergency first aid',
                    'Contact external emergency services if needed',
                    'Document all actions taken'
                ]),
                'steps_critical' => json_encode([
                    'Dispatch all available medical response personnel',
                    'Call emergency services (ambulance) immediately',
                    'Perform CPR/first aid as required',
                    'Clear routes for ambulance access',
                    'Assign escort to guide medical services',
                    'Notify family/emergency contacts',
                    'Secure the scene if injury was result of incident',
                    'Complete full documentation of response'
                ]),
                'escalation_triggers' => json_encode([
                    'Unconsciousness',
                    'Severe bleeding',
                    'Difficulty breathing',
                    'Suspected heart attack or stroke',
                    'Multiple casualties'
                ]),
                'notification_list' => json_encode([
                    'campus_clinic' => 'all',
                    'security_director' => 'high',
                    'emergency_services' => 'high,critical'
                ]),
                'requires_police_report' => false,
                'requires_medical_response' => true,
                'requires_evidence_collection' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Assault Response Protocol',
                'code' => 'ARP-1',
                'description' => 'Protocol for responding to physical or verbal assault incidents',
                'priority' => 'high',
                'category_id' => 2, // Assault category
                'required_teams' => json_encode(['response', 'investigation']),
                'required_resources' => json_encode(['first_aid_kit', 'camera', 'evidence_collection_kit']),
                'external_agencies' => json_encode(['police', 'campus_clinic']),
                'target_response_time' => 5,
                'resolution_time_target' => 60,
                'steps_medium' => json_encode([
                    'Dispatch security team to separate parties',
                    'Assess for injuries and provide first aid if needed',
                    'Interview involved parties separately',
                    'Document incident with photos and statements',
                    'Refer to student affairs/disciplinary committee',
                    'Offer counseling resources to affected individuals'
                ]),
                'steps_high' => json_encode([
                    'Dispatch multiple response teams immediately',
                    'Secure scene and separate involved parties',
                    'Assess injuries and arrange medical attention',
                    'Identify and isolate aggressor(s)',
                    'Interview witnesses immediately',
                    'Collect evidence and document scene',
                    'Notify campus management',
                    'Consider campus ban for aggressors',
                    'Arrange escort for victim if needed'
                ]),
                'steps_critical' => json_encode([
                    'Call police immediately',
                    'Dispatch all available security personnel',
                    'Secure scene and establish perimeter',
                    'Provide emergency medical care',
                    'Identify and detain perpetrator if safe to do so',
                    'Evacuate area if ongoing threat',
                    'Preserve all evidence',
                    'Activate campus emergency response team',
                    'Implement communication plan for campus community'
                ]),
                'escalation_triggers' => json_encode([
                    'Weapons involved',
                    'Serious injury',
                    'Multiple attackers',
                    'Hate crime elements',
                    'Sexual assault component'
                ]),
                'notification_list' => json_encode([
                    'security_director' => 'all',
                    'campus_management' => 'high,critical',
                    'police' => 'high,critical',
                    'counseling_services' => 'all'
                ]),
                'requires_police_report' => true,
                'requires_medical_response' => true,
                'requires_evidence_collection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suspicious Activity Protocol',
                'code' => 'SAP-1',
                'description' => 'Protocol for investigating and responding to suspicious activities',
                'priority' => 'medium',
                'category_id' => 3, // Suspicious Activity category
                'required_teams' => json_encode(['patrol', 'surveillance']),
                'required_resources' => json_encode(['radio', 'camera', 'binoculars']),
                'external_agencies' => null,
                'target_response_time' => 10,
                'resolution_time_target' => 60, // Added missing resolution_time_target
                'steps_low' => json_encode([
                    'Dispatch patrol officer to observe discreetly',
                    'Monitor situation without direct confrontation',
                    'Document observations and behaviors',
                    'Check if person has legitimate campus business',
                    'Increase patrols in area'
                ]),
                'steps_medium' => json_encode([
                    'Dispatch two patrol officers to area',
                    'Approach subject professionally and request identification',
                    'Determine reason for presence on campus',
                    'Document interaction completely',
                    'Escort off campus if no legitimate purpose',
                    'Add to watch list if appropriate'
                ]),
                'steps_high' => json_encode([
                    'Deploy multiple teams to establish surveillance',
                    'Position officers to intercept if subject attempts to flee',
                    'Approach with caution and request identification',
                    'Contact relevant departments to verify identity claims',
                    'Search campus access logs',
                    'Consider detention if warranted',
                    'Document all actions and findings'
                ]),
                'escalation_triggers' => json_encode([
                    'Subject refuses to identify',
                    'Attempts to access restricted areas',
                    'Matches description of previous security incidents',
                    'Displays threatening behavior',
                    'Appears to be monitoring security patterns'
                ]),
                'notification_list' => json_encode([
                    'security_director' => 'medium,high',
                    'zone_manager' => 'all'
                ]),
                'requires_police_report' => false,
                'requires_medical_response' => false,
                'requires_evidence_collection' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vandalism Response Protocol',
                'code' => 'VRP-1',
                'description' => 'Protocol for responding to property damage and vandalism',
                'priority' => 'medium',
                'category_id' => 4, // Vandalism category
                'required_teams' => json_encode(['patrol', 'investigation']),
                'required_resources' => json_encode(['camera', 'evidence_collection_kit', 'property_damage_form']),
                'external_agencies' => null,
                'target_response_time' => 15,
                'resolution_time_target' => 120,
                'steps_low' => json_encode([
                    'Document damage with photos',
                    'Estimate repair costs',
                    'Check nearby CCTV cameras',
                    'Complete incident report',
                    'Notify facilities management'
                ]),
                'steps_medium' => json_encode([
                    'Secure area to prevent further damage',
                    'Document extensively with photos and measurements',
                    'Collect any physical evidence',
                    'Interview potential witnesses',
                    'Review CCTV footage',
                    'Complete detailed damage assessment',
                    'Notify facilities and department head'
                ]),
                'steps_high' => json_encode([
                    'Establish perimeter around damaged area',
                    'Dispatch investigation team',
                    'Document all damage comprehensively',
                    'Collect all available evidence',
                    'Interview all potential witnesses',
                    'Review extended CCTV footage',
                    'Determine if targeted or random',
                    'Assess security vulnerabilities',
                    'Consider police report for extensive damage',
                    'Implement temporary security measures'
                ]),
                'escalation_triggers' => json_encode([
                    'Damage exceeds ₦100,000',
                    'Affects critical infrastructure',
                    'Contains threatening messages',
                    'Shows pattern of targeted vandalism',
                    'Impacts campus operations'
                ]),
                'notification_list' => json_encode([
                    'facilities_management' => 'all',
                    'security_director' => 'medium,high',
                    'department_head' => 'all',
                    'campus_management' => 'high'
                ]),
                'requires_police_report' => false,
                'requires_medical_response' => false,
                'requires_evidence_collection' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_protocols');
    }
};