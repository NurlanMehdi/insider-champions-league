<template>
  <div>
    <div v-if="predictions.length === 0" class="text-center text-muted">
      <p>No prediction data available.</p>
    </div>
    
    <div v-else>
      <div v-for="prediction in predictions" :key="prediction.team_id" class="prediction-item mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="team-info">
            <strong class="team-name">{{ prediction.team_name }}</strong>
          </div>
          <div class="prediction-points">
            <span class="points-value" :class="getPointsClass(prediction.predicted_points)">
              {{ prediction.predicted_points }} pts
            </span>
          </div>
        </div>
        
        <!-- Progress Bar showing prediction confidence -->
        <div class="progress" style="height: 8px;">
          <div 
            class="progress-bar" 
            :class="getProgressBarClass(prediction.predicted_points)"
            role="progressbar" 
            :style="{ width: getConfidenceWidth(prediction) + '%' }"
            :aria-valuenow="getConfidenceWidth(prediction)" 
            aria-valuemin="0" 
            aria-valuemax="100"
          ></div>
        </div>
        
        <div class="d-flex justify-content-between mt-2">
          <small class="text-muted">Current: {{ prediction.current_points }} pts</small>
          <small class="text-muted">GD: {{ formatGoalDifference(prediction.predicted_goal_difference) }}</small>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PredictionDisplay',
  props: {
    predictions: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    getConfidenceWidth(prediction) {
      // Convert predicted points to a confidence percentage (max 18 points for 6 games)
      const maxPossiblePoints = 18;
      return Math.min(100, (prediction.predicted_points / maxPossiblePoints) * 100);
    },
    
    getPointsClass(points) {
      if (points >= 15) return 'text-success fw-bold';
      if (points >= 10) return 'text-warning fw-bold';
      return 'text-danger fw-bold';
    },
    
    getProgressBarClass(points) {
      if (points >= 15) return 'bg-success';
      if (points >= 10) return 'bg-warning';
      return 'bg-danger';
    },
    
    formatGoalDifference(gd) {
      if (gd > 0) return `+${gd}`;
      return gd.toString();
    }
  }
};
</script>

<style scoped>
.prediction-item {
  padding: 0.75rem;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.7);
  border: 1px solid #e9ecef;
  transition: all 0.2s;
}

.prediction-item:hover {
  background-color: rgba(255, 255, 255, 0.9);
  transform: translateX(5px);
}

.team-name {
  font-size: 0.95em;
  color: #495057;
}

.points-value {
  font-size: 1.1em;
  font-weight: 600;
}

.progress {
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.progress-bar {
  border-radius: 4px;
  transition: width 0.6s ease;
}

small.text-muted {
  font-size: 0.8em;
}

.prediction-item:first-child {
  border-left: 4px solid #28a745;
}

.prediction-item:nth-child(2) {
  border-left: 4px solid #ffc107;
}

.prediction-item:nth-child(3) {
  border-left: 4px solid #17a2b8;
}

.prediction-item:last-child {
  border-left: 4px solid #dc3545;
}
</style> 