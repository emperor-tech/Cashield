<?php

namespace App\Models;

use App\Events\CategoryCreated;
use App\Events\CategoryUpdated;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class IncidentCategory
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $default_severity
 * @property string $default_priority
 * @property int $expected_response_time
 * @property string|null $icon
 * @property string $color
 * @property bool $requires_evidence
 * @property bool $requires_witness
 * @property bool $active
 * @property int|null $parent_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * 
 * @property IncidentCategory|null $parent
 * @property \Illuminate\Database\Eloquent\Collection $children
 * @property \Illuminate\Database\Eloquent\Collection $reports
 * @property ResponseProtocol|null $protocol
 */
class IncidentCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name', 'slug', 'description', 'default_severity', 'default_priority',
        'expected_response_time', 'icon', 'color', 'requires_evidence',
        'requires_witness', 'active', 'parent_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_evidence' => 'boolean',
        'requires_witness' => 'boolean',
        'active' => 'boolean',
        'expected_response_time' => 'integer',
    ];

    /**
     * The validation rules that the model attributes must pass.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'default_severity' => 'required|in:low,medium,high',
        'default_priority' => 'required|in:low,medium,high,critical',
        'expected_response_time' => 'required|integer|min:1',
        'icon' => 'nullable|string|max:255',
        'color' => 'required|string|max:255',
        'requires_evidence' => 'boolean',
        'requires_witness' => 'boolean',
        'active' => 'boolean',
        'parent_id' => 'nullable|exists:incident_categories,id'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating a new category
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Fire events and log changes
        static::created(function ($category) {
            event(new CategoryCreated($category));
            
            AuditService::logAction(
                'category_created',
                'incident_category',
                $category->id,
                ['category_name' => $category->name]
            );
        });

        static::updating(function ($category) {
            // Log changes to important attributes
            if ($category->isDirty(['name', 'default_severity', 'default_priority', 'active'])) {
                try {
                    AuditService::logModelChange('incident_category', $category->id, $category->getDirty());
                } catch (\Exception $e) {
                    Log::error("Failed to log audit for category update: " . $e->getMessage());
                }
            }
            
            // Update slug if name changed and slug wasn't manually set
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
            
            // Fire update event
            event(new CategoryUpdated($category));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(IncidentCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(IncidentCategory::class, 'parent_id');
    }

    /**
     * Get the active child categories.
     */
    public function activeChildren()
    {
        return $this->hasMany(IncidentCategory::class, 'parent_id')
                    ->where('active', true);
    }

    /**
     * Get all reports in this category.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'category_id');
    }

    /**
     * Get all reports in this category and its subcategories.
     */
    public function allReports()
    {
        $childIds = $this->getAllChildIds();
        $allIds = array_merge([$this->id], $childIds);
        
        return Report::whereIn('category_id', $allIds);
    }

    /**
     * Get the primary response protocol for this category.
     */
    public function protocol()
    {
        return $this->hasOne(ResponseProtocol::class, 'category_id')
                    ->where('active', true)
                    ->orderBy('version', 'desc');
    }

    /**
     * Get all protocols for this category.
     */
    public function protocols()
    {
        return $this->hasMany(ResponseProtocol::class, 'category_id')
                    ->where('active', true)
                    ->orderBy('version', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Category Hierarchy Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if this category has any children.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this category is a child of another category.
     *
     * @return bool
     */
    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get all ancestors of this category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $category = $this;
        
        while ($category->parent_id) {
            $parent = $category->parent;
            if ($parent) {
                $ancestors->push($parent);
                $category = $parent;
            } else {
                break;
            }
        }
        
        return $ancestors->reverse();
    }

    /**
     * Get all descendants of this category.
     *
     * @param bool $activeOnly Whether to include only active categories
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants(bool $activeOnly = true)
    {
        $descendants = collect();
        
        // Function to recursively collect descendants
        $collectDescendants = function ($category) use (&$collectDescendants, &$descendants, $activeOnly) {
            $children = $activeOnly ? $category->activeChildren : $category->children;
            
            foreach ($children as $child) {
                $descendants->push($child);
                $collectDescendants($child);
            }
        };
        
        $collectDescendants($this);
        
        return $descendants;
    }

    /**
     * Get all child IDs (recursive).
     *
     * @param bool $activeOnly Whether to include only active categories
     * @return array
     */
    public function getAllChildIds(bool $activeOnly = true): array
    {
        $ids = [];
        
        // Function to recursively collect child IDs
        $collectChildIds = function ($categoryId) use (&$collectChildIds, &$ids, $activeOnly) {
            $query = self::where('parent_id', $categoryId);
            
            if ($activeOnly) {
                $query->where('active', true);
            }
            
            $childIds = $query->pluck('id')->toArray();
            
            foreach ($childIds as $childId) {
                $ids[] = $childId;
                $collectChildIds($childId);
            }
        };
        
        $collectChildIds($this->id);
        
        return $ids;
    }

    /**
     * Get the category hierarchy as a breadcrumb array.
     *
     * @return array
     */
    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $ancestors = $this->getAncestors();
        
        foreach ($ancestors as $ancestor) {
            $breadcrumb[] = [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'slug' => $ancestor->slug
            ];
        }
        
        // Add current category
        $breadcrumb[] = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug
        ];
        
        return $breadcrumb;
    }

    /**
     * Create a child category.
     *
     * @param array $attributes
     * @return IncidentCategory
     */
    public function createChild(array $attributes): IncidentCategory
    {
        $attributes['parent_id'] = $this->id;
        
        // Inherit certain properties if not specified
        if (!isset($attributes['default_severity'])) {
            $attributes['default_severity'] = $this->default_severity;
        }
        
        if (!isset($attributes['default_priority'])) {
            $attributes['default_priority'] = $this->default_priority;
        }
        
        if (!isset($attributes['color'])) {
            $attributes['color'] = $this->color;
        }
        
        // Create and return the new child category
        return self::create($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Default Settings Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the appropriate protocol based on incident severity.
     *
     * @param string $severity The incident severity
     * @return ResponseProtocol|null
     */
    public function getProtocolForSeverity(string $severity): ?ResponseProtocol
    {
        // First try to get a protocol that matches this category and severity
        $protocol = ResponseProtocol::where('category_id', $this->id)
                                   ->where('priority', $severity)
                                   ->where('active', true)
                                   ->orderBy('version', 'desc')
                                   ->first();
        
        if ($protocol) {
            return $protocol;
        }
        
        // If no specific protocol for this severity, return the default protocol
        return $this->protocol;
    }

    /**
     * Apply default settings to a report.
     *
     * @param Report $report The report to apply settings to
     * @return bool
     */
    public function applyDefaultsToReport(Report $report): bool
    {
        $report->category_id = $this->id;
        
        // Apply default severity if not set
        if (!$report->severity) {
            $report->severity = $this->default_severity;
        }
        
        // Apply default priority if not set
        if (!$report->priority_level) {
            $report->priority_level = $this->default_priority;
        }
        
        // Set evidence requirement
        if ($this->requires_evidence) {
            // This would be implemented based on how evidence is tracked in the Report model
            $report->requires_evidence = true;
        }
        
        // Apply protocol if available
        $protocol = $this->getProtocolForSeverity($report->severity);
        if ($protocol) {
            $report->protocol_id = $protocol->id;
        }
        
        return $report->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get incident statistics for this category.
     *
     * @param int $days Number of days to include
     * @param bool $includeSubcategories Whether to include subcategories
     * @return array
     */
    public function getIncidentStats(int $days = 30, bool $includeSubcategories = true): array
    {
        $query = $includeSubcategories ? $this->allReports() : $this->reports();
        
        $startDate = now()->subDays($days);
        $reports = $query->where('created_at', '>=', $startDate)->get();
        
        // Total incidents
        $total = $reports->count();
        
        // Severity breakdown
        $severityStats = [
            'high' => $reports->where('severity', 'high')->count(),
            'medium' => $reports->where('severity', 'medium')->count(),
            'low' => $reports->where('severity', 'low')->count()
        ];
        
        // Status breakdown
        $statusStats = [
            'open' => $reports->where('status', 'open')->count(),
            'in_progress' => $reports->where('status', 'in_progress')->count(),
            'resolved' => $reports->where('status', 'resolved')->count()
        ];
        
        // Resolution time statistics
        $resolutionTimes = $reports->whereNotNull('resolution_time')->map(function ($report) {
            $created = $report->incident_date ?? $report->created_at;
            return $created->diffInMinutes($report->resolution_time);
        });
        
        $avgResolutionTime = $resolutionTimes->isEmpty() ? 0 : $resolutionTimes->avg();
        
        // Response time statistics
        $responseTimes = $reports->whereNotNull('response_time')->map(function ($report) {
            $created = $report->incident_date ?? $report->created_at;
            return $created->diffInMinutes($report->response_time);
        });
        
        $avgResponseTime = $responseTimes->isEmpty() ? 0 : $responseTimes->avg();
        
        // Trend data by day
        $trendData = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = $reports->filter(function ($report) use ($date) {
                return $report->created_at->format('Y-m-d') === $date;
            })->count();
            
            $trendData[$date] = $count;
        }
        
        return [
            'total_incidents' => $total,
            'severity' => $severityStats,
            'status' => $statusStats,
            'avg_resolution_time' => round($avgResolutionTime),
            'avg_response_time' => round($avgResponseTime),
            'trend_data' => $trendData,
            'days' => $days,
            'category_id' => $this->id,
            'category_name' => $this->name
        ];
    }

    /**
     * Get subcategory distribution statistics.
     *
     * @param int $days Number of days to include
     * @return array
     */
    public function getSubcategoryStats(int $days = 30): array
    {
        if (!$this->hasChildren()) {
            return [];
        }
        
        $startDate = now()->subDays($days);
        $childIds = $this->getAllChildIds();
        
        $subcategoryStats = Report::whereIn('category_id', $childIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('category_id, COUNT(*) as count')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                $category = self::find($item->category_id);
                return [
                    'id' => $item->category_id,
                    'name' => $category ? $category->name : 'Unknown',
                    'count' => $item->count,
                    'color' => $category ? $category->color : '#cccccc'
                ];
            });
        
        return $subcategoryStats->toArray();
    }

    /**
     * Get the response time compliance rate.
     *
     * @param int $days Number of days to include
     * @return float Percentage of reports that met the target response time
     */
    public function getResponseTimeComplianceRate(int $days = 30): float
    {
        $startDate = now()->subDays($days);
        $reports = $this->allReports()
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('response_time')
            ->get();
        
        if ($reports->isEmpty()) {
            return 100.0; // No reports to evaluate
        }
        
        $compliantCount = 0;
        
        foreach ($reports as $report) {
            $targetTime = $this->expected_response_time;
            $actualTime = $report->getResponseTimeInMinutes();
            
            if ($actualTime <= $targetTime) {
                $compliantCount++;
            }
        }
        
        return round(($compliantCount / $reports->count()) * 100, 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Icon and Color Management Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the icon HTML.
     *
     * @return string
     */
    public function getIconHtml(): string
    {
        if (empty($this->icon)) {
            return '<i class="fas fa-exclamation-circle"></i>';
        }
        
        return '<i class="' . e($this->icon) . '"></i>';
    }

    /**
     * Get the severity class for display.
     *
     * @return string
     */
    public function getSeverityClass(): string
    {
        return match($this->default_severity) {
            'high' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
        };
    }

    /**
     * Get the color for severity display.
     *
     * @return string
     */
    public function getSeverityColor(): string
    {
        return match($this->default_severity) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray'
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Validate category attributes.
     *
     * @param array $attributes
     * @return \Illuminate\Validation\Validator
     */
    public static function validator(array $attributes)
    {
        return Validator::make($attributes, self::$rules);
    }

    /**
     * Check if a category is valid.
     *
     * @return array Validation results
     */
    public function validate(): array
    {
        $validator = self::validator($this->toArray());
        
        $issues = [];
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $issues[] = $error;
            }
        }
        
        // Additional business logic validations
        if ($this->parent_id === $this->id) {
            $issues[] = "Category cannot be its own parent";
        }
        
        // Check for circular references in parent-child relationships
        if ($this->id && $this->parent_id) {
            $parent = $this->parent;
            while ($parent) {
                if ($parent->parent_id === $this->id) {
                    $issues[] = "Circular reference detected in category hierarchy";
                    break;
                }
                $parent = $parent->parent;
            }
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'category_id' => $this->id,
            'category_name' => $this->name,
            'validated_at' => now()->toIso8601String()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get root (top-level) categories.
     *
     * @param bool $activeOnly Whether to include only active categories
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRootCategories(bool $activeOnly = true)
    {
        $query = self::whereNull('parent_id');
        
        if ($activeOnly) {
            $query->where('active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get full category tree.
     *
     * @param bool $activeOnly Whether to include only active categories
     * @return array
     */
    public static function getCategoryTree(bool $activeOnly = true): array
    {
        $rootCategories = self::getRootCategories($activeOnly);
        $tree = [];
        
        foreach ($rootCategories as $rootCategory) {
            $tree[] = self::buildCategoryTreeNode($rootCategory, $activeOnly);
        }
        
        return $tree;
    }

    /**
     * Build a tree node for a category and its children.
     *
     * @param IncidentCategory $category
     * @param bool $activeOnly Whether to include only active categories
     * @return array
     */
    protected static function buildCategoryTreeNode(IncidentCategory $category, bool $activeOnly = true): array
    {
        $node = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'default_severity' => $category->default_severity,
            'color' => $category->color,
            'icon' => $category->icon,
            'children' => []
        ];
        
        $children = $activeOnly ? $category->activeChildren : $category->children;
        
        foreach ($children as $child) {
            $node['children'][] = self::buildCategoryTreeNode($child, $activeOnly);
        }
        
        return $node;
    }

    /**
     * Find a category by slug.
     *
     * @param string $slug
     * @param bool $activeOnly Whether to include only active categories
     * @return IncidentCategory|null
     */
    public static function findBySlug(string $slug, bool $activeOnly = true): ?IncidentCategory
    {
        $query = self::where('slug', $slug);
        
        if ($activeOnly) {
            $query->where('active', true);
        }
        
        return $query->first();
    }

    /**
     * Get the most frequently reported categories.
     *
     * @param int $limit Maximum number of categories to return
     * @param int $days Number of days to include
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMostReportedCategories(int $limit = 5, int $days = 30)
    {
        $startDate = now()->subDays($days);
        
        return self::where('active', true)
            ->withCount(['reports' => function($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->having('reports_count', '>', 0)
            ->orderBy('reports_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the highest risk categories based on severity and incident count.
     *
     * @param int $limit Maximum number of categories to return
     * @param int $days Number of days to include
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHighRiskCategories(int $limit = 5, int $days = 30)
    {
        $startDate = now()->subDays($days);
        $categories = self::where('active', true)->get();
        
        // Calculate risk score for each category
        foreach ($categories as $category) {
            $reports = $category->allReports()
                ->where('created_at', '>=', $startDate)
                ->get();
            
            $highCount = $reports->where('severity', 'high')->count();
            $mediumCount = $reports->where('severity', 'medium')->count();
            $lowCount = $reports->where('severity', 'low')->count();
            
            // Calculate weighted risk score
            $category->risk_score = ($highCount * 5) + ($mediumCount * 2) + $lowCount;
        }
        
        // Sort by risk score and take top N
        return $categories->sortByDesc('risk_score')->take($limit);
    }
}

