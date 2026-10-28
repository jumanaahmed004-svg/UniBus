<?php
/**
 * Trip form fields shared by insert.php and update.php.
 * Expects: $trip (array of values), $drivers (list), $booked (int seats already booked)
 */
$destinations = array_column(q_all('SELECT DISTINCT destination FROM trips ORDER BY destination'), 'destination');
?>
<?= csrf_field() ?>
<div class="form-row">
    <div class="form-group">
        <label for="destination">Destination</label>
        <input type="text" id="destination" name="destination" list="dest-list" value="<?= e($trip['destination']) ?>" maxlength="60" required>
        <datalist id="dest-list">
            <?php foreach ($destinations as $d): ?><option value="<?= e($d) ?>"><?php endforeach; ?>
        </datalist>
    </div>
    <div class="form-group">
        <label for="bus_number">Bus number</label>
        <input type="text" id="bus_number" name="bus_number" value="<?= e($trip['bus_number']) ?>" placeholder="BUS-111" maxlength="20" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" value="<?= e($trip['date']) ?>" required>
    </div>
    <div class="form-group">
        <label for="time">Time</label>
        <input type="time" id="time" name="time" value="<?= e($trip['time']) ?>" required>
        <span class="hint">Before 12:00 = home → university. 12:00 and later = university → home.</span>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label for="driver_id">Driver</label>
        <select id="driver_id" name="driver_id">
            <option value="">Not assigned yet</option>
            <?php foreach ($drivers as $d): ?>
                <option value="<?= (int) $d['driver_id'] ?>" <?= (int) $trip['driver_id'] === (int) $d['driver_id'] ? 'selected' : '' ?>>
                    <?= e($d['driver_name']) ?> (<?= e($d['phone']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="seats_total">Total seats</label>
        <input type="number" id="seats_total" name="seats_total" min="<?= max(1, $booked) ?>" max="100" value="<?= (int) $trip['seats_total'] ?>" required>
        <span class="hint">
            <?= $booked ? "$booked seat(s) are already booked. Available seats are calculated automatically." : 'Available seats are calculated automatically.' ?>
        </span>
    </div>
</div>
