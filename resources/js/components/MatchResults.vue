<template>
  <div>
    <div v-if="matches.length === 0" class="text-center text-muted p-4">
      <p>No matches for this week.</p>
    </div>
    
    <div v-for="match in matches" :key="match.id" class="match-result mb-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
          <div class="row align-items-center">
            <!-- Home Team -->
            <div class="col-4 text-end">
              <div class="team-info">
                <span class="team-name fw-bold">{{ match.home_team }}</span>
              </div>
            </div>
            
            <!-- Score -->
            <div class="col-4 text-center">
              <div v-if="match.is_played" class="score-container">
                <div class="score-display">
                  {{ match.home_score }} - {{ match.away_score }}
                </div>
              </div>
              <div v-else class="vs-display">
                <span class="vs-text">VS</span>
                <small class="text-muted d-block">Not played</small>
              </div>
            </div>
            
            <!-- Away Team -->
            <div class="col-4">
              <div class="team-info">
                <span class="team-name fw-bold">{{ match.away_team }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MatchResults',
  props: {
    matches: {
      type: Array,
      default: () => []
    }
  }
};
</script>

<style scoped>
.match-result {
  transition: transform 0.2s;
}

.match-result:hover {
  transform: translateY(-1px);
}

.team-name {
  font-size: 0.95em;
}

.score-display {
  font-size: 1.5em;
  font-weight: bold;
  color: #007bff;
  padding: 0.5em;
  border-radius: 8px;
}

.vs-display {
  padding: 0.5em;
}

.vs-text {
  font-size: 1.2em;
  font-weight: 600;
  color: #6c757d;
}

.team-info {
  display: flex;
  align-items: center;
  justify-content: center;
}

.card {
  border-radius: 12px;
  transition: all 0.2s;
}

.card:hover {
  box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1) !important;
}
</style> 