<template>
  <div class="table-responsive">
    <table class="table table-sm">
      <thead class="table-primary">
        <tr>
          <th>Teams</th>
          <th class="text-center">PTS</th>
          <th class="text-center">P</th>
          <th class="text-center">W</th>
          <th class="text-center">D</th>
          <th class="text-center">L</th>
          <th class="text-center">GD</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(team, index) in standings" :key="team.id" :class="getRowClass(index)">
          <td>
            <strong>{{ team.name }}</strong>
          </td>
          <td class="text-center">
            <span class="badge bg-primary">{{ team.points }}</span>
          </td>
          <td class="text-center">{{ team.played }}</td>
          <td class="text-center">{{ team.wins }}</td>
          <td class="text-center">{{ team.draws }}</td>
          <td class="text-center">{{ team.losses }}</td>
          <td class="text-center" :class="getGdClass(team.goal_difference)">
            {{ formatGoalDifference(team.goal_difference) }}
          </td>
        </tr>
      </tbody>
    </table>
    
    <div v-if="standings.length === 0" class="text-center text-muted p-4">
      <p>No league data available. Initialize the league first.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LeagueTable',
  props: {
    standings: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    getRowClass(index) {
      // Highlight the leader
      if (index === 0 && this.standings.length > 0) {
        return 'table-success';
      }
      // Highlight bottom team
      if (index === this.standings.length - 1 && this.standings.length > 1) {
        return 'table-danger';
      }
      return '';
    },
    getGdClass(goalDifference) {
      if (goalDifference > 0) {
        return 'text-success fw-bold';
      } else if (goalDifference < 0) {
        return 'text-danger fw-bold';
      }
      return 'text-muted';
    },
    formatGoalDifference(gd) {
      if (gd > 0) {
        return `+${gd}`;
      }
      return gd.toString();
    }
  }
};
</script>

<style scoped>
.table th {
  border-bottom: 2px solid #007bff;
  font-weight: 600;
  font-size: 0.9em;
}

.table td {
  vertical-align: middle;
  font-size: 0.9em;
}

.badge {
  font-size: 0.8em;
  padding: 0.4em 0.6em;
}

.table-responsive {
  border-radius: 8px;
  overflow: hidden;
}
</style> 