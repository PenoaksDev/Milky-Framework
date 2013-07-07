package com.chiorichan.command.defaults;

import java.util.ArrayList;
import java.util.List;

import org.apache.commons.lang.Validate;
import com.chiorichan.ChioriFramework;
import com.chiorichan.ChatColor;
import com.chiorichan.OfflinePlayer;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;
import com.chiorichan.entity.Player;
import com.chiorichan.util.StringUtil;

import com.google.common.collect.ImmutableList;

public class DeopCommand extends VanillaCommand {
    public DeopCommand() {
        super("deop");
        this.description = "Takes the specified player's operator status";
        this.usageMessage = "/deop <player>";
        this.setPermission("bukkit.command.op.take");
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;
        if (args.length != 1 || args[0].length() == 0) {
            sender.sendMessage(ChatColor.RED + "Usage: " + usageMessage);
            return false;
        }

        OfflinePlayer player = ChioriFramework.getOfflinePlayer(args[0]);
        player.setOp(false);

        if (player instanceof Player) {
            ((Player) player).sendMessage(ChatColor.YELLOW + "You are no longer op!");
        }

        Command.broadcastCommandMessage(sender, "De-opped " + args[0]);
        return true;
    }

    @Override
    public List<String> tabComplete(CommandSender sender, String alias, String[] args) throws IllegalArgumentException {
        Validate.notNull(sender, "Sender cannot be null");
        Validate.notNull(args, "Arguments cannot be null");
        Validate.notNull(alias, "Alias cannot be null");

        if (args.length == 1) {
            List<String> completions = new ArrayList<String>();
            for (OfflinePlayer player : ChioriFramework.getOfflinePlayers()) {
                String playerName = player.getName();
                if (player.isOp() && StringUtil.startsWithIgnoreCase(playerName, args[0])) {
                    completions.add(playerName);
                }
            }
            return completions;
        }
        return ImmutableList.of();
    }
}
