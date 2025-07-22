<template>
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="week-header">
          <h1 class="mb-2">Premier League Simulation</h1>
          <p class="mb-0">Week {{ currentWeek }} of {{ totalWeeks }}</p>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="btn-group" role="group">
          <button @click="initializeLeague" class="btn btn-primary" :disabled="loading">
            Initialize League
          </button>
          <button @click="simulateNext" class="btn simulate-btn" :disabled="loading || isSeasonComplete">
            {{ loading ? 'Simulating...' : 'Next Week' }}
          </button>
          <button @click="simulateAll" class="btn btn-success" :disabled="loading">
            {{ loading ? 'Simulating...' : 'Play All' }}
          </button>
          <button @click="resetLeague" class="btn btn-warning" :disabled="loading">
            Reset League
          </button>
        </div>
      </div>
      
      <div class="col-md-6 mb-3">
        <div class="week-selector">
          <label for="weekSelect" class="form-label">View Week:</label>
          <select id="weekSelect" v-model="selectedWeek" @change="loadWeek(selectedWeek)" class="form-select">
            <option v-for="week in totalWeeks" :key="week" :value="week">Week {{ week }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6 mb-4">
        <div class="league-table p-3">
          <h3 class="mb-3">Premier League Table</h3>
          <LeagueTable :standings="standings" />
        </div>
      </div>

      <div class="col-lg-6 mb-4">
        <div class="row">
          <div class="col-12 mb-4">
            <div class="match-card p-3">
              <h3 class="mb-3">Week {{ selectedWeek }} Fixtures</h3>
              <MatchResults :matches="currentWeekMatches" />
            </div>
          </div>
          
          <div class="col-12">
            <div class="prediction-card p-3" v-if="currentWeek > 10 && predictions.length > 0">
              <h4 class="mb-3">Title Race Predictions</h4>
              <PredictionDisplay :predictions="predictions.slice(0, 6)" />
            </div>
            <div v-else class="prediction-card p-3">
              <h4>Title Race Predictions</h4>
              <p class="text-muted">Available after Week 10</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4" v-if="isSeasonComplete">
      <div class="col-12">
        <div class="alert alert-success text-center">
          <h4>🏆 Season Complete!</h4>
          <p class="mb-0">Congratulations to <strong>{{ standings[0]?.name }}</strong> - Premier League Champions!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import LeagueTable from './LeagueTable.vue';
import MatchResults from './MatchResults.vue';
import PredictionDisplay from './PredictionDisplay.vue';

export default {
  name: 'LeagueApp',
  components: {
    LeagueTable,
    MatchResults,
    PredictionDisplay
  },
  data() {
    return {
      standings: [],
      currentWeekMatches: [],
      predictions: [],
      currentWeek: 1,
      selectedWeek: 1,
      totalWeeks: 38,
      loading: false
    };
  },
  computed: {
    isSeasonComplete() {
      return this.currentWeek > this.totalWeeks;
    }
  },
  mounted() {
    this.loadLeagueData();
  },
  methods: {
    async loadLeagueData() {
      try {
        const response = await axios.get('/api/league');
        const data = response.data.data || response.data;
        
        this.standings = data.standings || [];
        this.currentWeek = data.current_week || 1;
        this.predictions = data.predictions || [];
        this.selectedWeek = this.currentWeek;
        
        // Only load week data if we have a valid current week
        if (this.currentWeek && this.currentWeek > 0) {
          await this.loadWeek(this.currentWeek);
        }
      } catch (error) {
        console.error('Error loading league data:', error);
        // Set default values on error
        this.standings = [];
        this.currentWeek = 1;
        this.predictions = [];
        this.selectedWeek = 1;
      }
    },
    async loadWeek(week) {
      // Validate week parameter
      if (!week || week < 1 || week > this.totalWeeks) {
        console.warn(`Invalid week parameter: ${week}`);
        return;
      }
      
      try {
        this.selectedWeek = week;
        const response = await axios.get(`/api/league/week/${week}`);
        const data = response.data.data || response.data;
        
        this.currentWeekMatches = data.matches || [];
      } catch (error) {
        console.error('Error loading week data:', error);
        this.currentWeekMatches = [];
      }
    },
    async initializeLeague() {
      this.loading = true;
      try {
        await axios.post('/api/league/initialize');
        await this.loadLeagueData();
      } catch (error) {
        console.error('Error initializing league:', error);
      } finally {
        this.loading = false;
      }
    },
    async simulateNext() {
      this.loading = true;
      try {
        await axios.post('/api/league/simulate-week', { week: this.currentWeek });
        await this.loadLeagueData();
      } catch (error) {
        console.error('Error simulating week:', error);
      } finally {
        this.loading = false;
      }
    },
    async simulateAll() {
      this.loading = true;
      try {
        const response = await axios.post('/api/league/simulate-all');
        
        if (response.data.data) {
          const data = response.data.data;
          console.log(`Simulation complete! ${data.matches_simulated} matches simulated in ${data.execution_time}`);
          console.log(`Average time per match: ${data.average_per_match}`);
        }
        
        await this.loadLeagueData();
      } catch (error) {
        console.error('Error simulating all matches:', error);
      } finally {
        this.loading = false;
      }
    },
    async resetLeague() {
      this.loading = true;
      try {
        await axios.post('/api/league/reset');
        await this.loadLeagueData();
      } catch (error) {
        console.error('Error resetting league:', error);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.week-selector {
  display: flex;
  align-items: center;
  gap: 10px;
}

.week-selector .form-label {
  margin-bottom: 0;
  white-space: nowrap;
}

.btn-group .btn {
  margin-right: 0.5rem;
}

.btn-group .btn:last-child {
  margin-right: 0;
}

@media (max-width: 768px) {
  .btn-group {
    flex-wrap: wrap;
  }
  
  .btn-group .btn {
    margin-bottom: 0.5rem;
  }
}
</style> 