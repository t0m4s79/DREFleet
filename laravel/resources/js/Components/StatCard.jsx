import React from "react";
import { Card, CardContent, Typography, Box } from "@mui/material";
import { green, blue, red, orange } from "@mui/material/colors";
import { TrendingUp, TrendingDown, Assessment } from "@mui/icons-material";

const StatCard = ({ title, value, description, icon, color }) => {
    return (
        <Card variant="outlined" sx={{ minWidth: 150, borderRadius: 3, boxShadow: 1 }}>
            <CardContent>
                <Box display="flex" alignItems="center">
                    <Box
                        sx={{
                            width: 50,
                            height: 50,
                            borderRadius: "50%",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                        }}
                    >
                        {icon}
                    </Box>
                    <Box>
                        <Typography variant="h6" color="textSecondary">
                            {title}
                        </Typography>
                        <Typography variant="h4" sx={{ fontWeight: "bold" }}>
                            {value}
                        </Typography>
                    </Box>
                </Box>
                <Typography variant="body2" color="textSecondary" mt={1}>
                    {description}
                </Typography>
            </CardContent>
        </Card>
    );
};

// Example usage with different stats
const DashboardStats = () => {
  return (
    <Box display="grid" gridTemplateColumns="repeat(auto-fit, minmax(250px, 1fr))" gap={2}>
      <StatCard
        title="Total Vehicles"
        value="150"
        description="Currently active in the fleet"
        icon={<Assessment sx={{ color: blue[700] }} />}
        color={blue}
      />
      <StatCard
        title="Orders Completed"
        value="1,280"
        description="Successfully delivered"
        icon={<TrendingUp sx={{ color: green[700] }} />}
        color={green}
      />
      <StatCard
        title="Orders Pending"
        value="52"
        description="Awaiting approval"
        icon={<TrendingDown sx={{ color: red[700] }} />}
        color={red}
      />
      <StatCard
        title="Maintenance Requests"
        value="23"
        description="Currently in progress"
        icon={<Assessment sx={{ color: orange[700] }} />}
        color={orange}
      />
    </Box>
  );
};

export default StatCard;