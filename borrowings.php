<?php if ($message !== ""): ?>
            <p class="message">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <p class="error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <div class="card">
            <h2>Borrow a Book</h2>

            <form method="POST">
                <div class="form-grid">
                    <div>
                        <label for="book_id">Select Book</label>
                        <select name="book_id" id="book_id" required>
                            <option value="">Choose a book</option>
                            <?php foreach ($books as $book): ?>
                                <option value="<?= (int) $book['id'] ?>"
                                    <?= (int) $book['available'] < 1 ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars($book['title']) ?>
                                    (Available: <?= (int) $book['available'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="member_id">Select Member</label>
                        <select name="member_id" id="member_id" required>
                            <option value="">Choose a member</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= (int) $member['id'] ?>">
                                    <?= htmlspecialchars($member['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="borrow_date">Borrow Date</label>
                        <input type="date" name="borrow_date"
                               id="borrow_date"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div>
                        <label for="due_date">Due Date</label>
                        <input type="date" name="due_date"
                               id="due_date"
                               value="<?= date('Y-m-d', strtotime('+14 days')) ?>"
                               required>
                    </div>
                </div>

                <p>
                    <button type="submit" name="borrow_book">
                        Record Borrowing
                    </button>
                </p>
            </form>
        </div>

        <div class="card">
            <h2>Borrowing Records</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book</th>
                            <th>Member</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($borrowings as $item): ?>
                        <tr>
                            <td><?= (int) $item['id'] ?></td>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= htmlspecialchars($item['full_name']) ?></td>
                            <td><?= htmlspecialchars($item['borrow_date']) ?></td>
                            <td><?= htmlspecialchars($item['due_date']) ?></td>
                            <td><?= htmlspecialchars($item['return_date'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($item['status']) ?></td>
                            <td>
                                <?php if ($item['status'] === 'Borrowed'): ?>
                                    <form method="POST"
                                          onsubmit="return confirm('Mark this book as returned?');">
                                        <input type="hidden"
                                               name="borrowing_id"
                                               value="<?= (int) $item['id'] ?>">
                                        <button class="return-button"
                                                type="submit"
                                                name="return_book">
                                            Return Book
                                        </button>
                                    </form>
                                <?php else: ?>
                                    Returned
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($borrowings) === 0): ?>
                        <tr>
                            <td colspan="8">No borrowing records yet.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

</div>
</body>
</html>