<?php
/**
 * Analytics Tab - Official Dashboard
 */
?>
<div class="row g-4 mb-5">
    <div class="col-lg-6">
        <div class="glass-card p-5 border-0 shadow-sm h-100 mb-4">
            <h5 class="fw-bold mb-5 fs-4" style="font-family: 'Outfit', sans-serif;">Incident Category Breakdown</h5>
            <div style="height: 400px;">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="glass-card p-5 border-0 shadow-sm h-100 mb-4">
            <h5 class="fw-bold mb-5 fs-4" style="font-family: 'Outfit', sans-serif;">Reporting Trends (Last 7 Days)</h5>
            <div style="height: 400px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="glass-card p-5 border-0 shadow-sm fade-in">
    <h5 class="fw-bold mb-4 fs-4" style="font-family: 'Outfit', sans-serif;">Environmental Impact Metrics</h5>
    <div class="row g-4 text-center">
        <?php 
        $types = ['pollution', 'illegal_dumping', 'flood', 'fire_hazard', 'noise_pollution', 'other'];
        foreach($types as $t):
            $query = "SELECT COUNT(*) as count FROM reports WHERE incident_type = :t";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":t", $t);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        ?>
        <div class="col-6 col-md-2">
            <div class="p-4 bg-light rounded-4 border shadow-none">
                <div class="fw-bold text-dark fs-3"><?php echo $count; ?></div>
                <div class="small text-muted text-uppercase fw-bold"><?php echo str_replace('_', ' ', $t); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
