# Debug Rankings Points Issue

## Steps to debug:

1. **Open Browser Console (F12)**
   - Go to Rankings tab in dashboard
   - Check Network tab for `/homeyard/tournaments/19/rankings` request
   - Look at Response JSON - check if `rankings[0].points` is 0 or has value

2. **Check Database Directly**
   ```sql
   SELECT id, athlete_id, points, matches_won, matches_lost 
   FROM group_standings 
   WHERE points > 0 
   LIMIT 5;
   ```
   - If all results show points = 0, then data in DB is 0
   - If data has points > 0, then query/API is filtering wrong

3. **Test Query in Code**
   ```php
   // Add this in controller temporarily
   $test = GroupStanding::select('id', 'athlete_id', 'points')
       ->whereHas('group', function($q) { $q->where('tournament_id', 19); })
       ->limit(5)
       ->get();
   dd($test);
   ```

4. **Possible Issues:**
   - Select statement still missing fields (unlikely now)
   - Database data is actually 0
   - Collection to array conversion issue (already fixed with ->values()->all())
   - JSON response casting issue
